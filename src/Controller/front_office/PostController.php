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

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Simulate a logged-in user (optional)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        $posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('front_office/post/index.html.twig', [
            'posts' => $posts,
            'user' => $user, // Pass the user to the template
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Simulate a logged-in user (optional)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        $post = new Post();
        $form = $this->createForm(\App\Form\PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front_office/post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'user' => $user, // Pass the user to the template
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

        // Simulate a logged-in user (optional)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        // Handle Comment Creation
        $newComment = new Comment();
        $newComment->setPost($post); // Link the comment to the post

        if ($user) {
            $newComment->setUserId($user['id']); // Example user ID
        } else {
            $newComment->setUserId(1); // Default user ID for simulation
        }

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
            'user' => $user, // Pass the user to the template
        ]);
    }

    #[Route('/{postId}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Simulate a logged-in user (optional)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        $form = $this->createForm(\App\Form\PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front_office/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'user' => $user, // Pass the user to the template
        ]);
    }

    #[Route('/{postId}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Simulate a logged-in user (optional)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        if ($this->isCsrfTokenValid('delete' . $post->getPostId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/comment/{commentId}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
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

    /**
     * Helper method to simulate a logged-in user.
     *
     * @param bool $simulateUser Whether to simulate a user or not
     * @return array|null Simulated user data or null
     */
    private function getSimulatedUser(bool $simulateUser): ?array
    {
        if ($simulateUser) {
            return [
                'id' => 1, // Simulated user ID
                'username' => 'John Doe',
                'profile_picture' => 'default_profile_pic.jpg',
            ];
        }

        return null; // No user logged in
    }
}