<?php

namespace App\Controller\front_office;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search'); // get the 'search' input from URL
        $postRepository = $entityManager->getRepository(Post::class);

        if ($search) {
            $queryBuilder = $postRepository->createQueryBuilder('p');
            $queryBuilder
                ->where('p.title LIKE :search')
                ->orWhere('p.content LIKE :search')
                ->orWhere('p.category LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('p.postId', 'DESC');

            $posts = $queryBuilder->getQuery()->getResult();
        } else {
            $posts = $postRepository->findBy([], ['postId' => 'DESC']);
        }

        return $this->render('front_office/post/index.html.twig', [
            'posts' => $posts,
        ]);
    }


    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        $post = new Post();
        $form = $this->createForm(\App\Form\PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user ID for the post (assuming your User entity has an `id` field)
            $post->setUserId($user->getUserId());

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front_office/post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{postId}', name: 'app_post_show', methods: ['GET', 'POST'])]
    public function show(Request $request, EntityManagerInterface $entityManager, int $postId): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $newComment = new Comment();
        $newComment->setPost($post);
        $newComment->setUserId($user->getUserId());

        $form = $this->createFormBuilder($newComment)
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Écrivez votre commentaire ici...',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $content = $newComment->getContent();

                // Check if content contains bad words
                if ($this->containsBadWords($content)) {
                    $form->addError(new \Symfony\Component\Form\FormError('Votre commentaire contient des mots inappropriés.'));

                    return $this->render('front_office/post/show.html.twig', [
                        'post' => $post,
                        'form' => $form->createView(),
                    ]);
                }

                $entityManager->persist($newComment);
                $entityManager->flush();

                return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
            }
        }

        return $this->render('front_office/post/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{postId}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        // Check if the authenticated user is the owner of the post
        if ($post->getUserId() !== $user->getUserId()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à modifier cette publication.');
        }

        $form = $this->createForm(\App\Form\PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front_office/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{postId}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        // Check if the authenticated user is the owner of the post
        if ($post->getUserId() !== $user->getUserId()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette publication.');
        }

        if ($this->isCsrfTokenValid('delete' . $post->getPostId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/comment/{commentId}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        // Check if the authenticated user is the owner of the comment
        if ($comment->getUserId() !== $user->getUserId()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce commentaire.'], Response::HTTP_FORBIDDEN);
        }

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('edit-comment' . $comment->getCommentId(), $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        // Get the new content from the request body
        $data = json_decode($request->getContent(), true);
        $newContent = trim($data['content'] ?? '');

        if (empty($newContent)) {
            return $this->json(['error' => 'Le contenu du commentaire ne peut pas être vide.'], Response::HTTP_BAD_REQUEST);
        }

        // Update the comment content
        $comment->setContent($newContent);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
    private function containsBadWords(string $text): bool
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://www.purgomalum.com/service/containsprofanity', [
                'query' => ['text' => $text],
            ]);

            $result = trim((string) $response->getBody());

            return $result === 'true';
        } catch (\Exception $e) {
            // Log or ignore API failure
            return false;
        }
    }

}