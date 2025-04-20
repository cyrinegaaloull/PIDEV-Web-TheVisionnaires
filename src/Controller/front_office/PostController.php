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
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all posts
        $posts = $entityManager->getRepository(Post::class)->findAll();

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
        // Fetch the Post entity based on postId
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }

        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        // Handle Comment Creation
        $newComment = new Comment();
        $newComment->setPost($post); // Link the comment to the post
        $newComment->setUserId($user->getUserId()); // Set the user ID for the comment

        $form = $this->createFormBuilder($newComment)
            ->add('content', TextareaType::class, [
                'label' => false, // No label for the textarea
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Écrivez votre commentaire ici...',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($newComment);
            $entityManager->flush();

            // Redirect back to the same page to avoid resubmission issues
            return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
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
}