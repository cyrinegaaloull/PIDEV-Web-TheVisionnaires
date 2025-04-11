<?php

namespace App\Controller\front_office;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\PostType;
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
        $form = $this->createForm(PostType::class, $post);
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
        // Fetch the Post entity
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
        $newComment->setUserId(1); // Set default userId to 1

        $form = $this->createFormBuilder($newComment)
            ->add('content', TextareaType::class, [
                'label' => 'Ajouter un commentaire',
                'attr' => ['rows' => 3],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($newComment);
            $entityManager->flush();

            // Redirect back to the same page to avoid resubmission issues
            return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
        }

        // Handle Comment Deletion
        if ($request->isMethod('POST') && $request->get('action') === 'delete_comment') {
            $commentId = $request->get('commentId');
            $comment = $entityManager->getRepository(Comment::class)->find($commentId);

            if ($comment && $comment->getPost()->getPostId() === $post->getPostId()) {
                if ($this->isCsrfTokenValid('delete' . $commentId, $request->get('_token'))) {
                    $entityManager->remove($comment);
                    $entityManager->flush();
                }
            }

            // Redirect back to the same page
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

        $form = $this->createForm(PostType::class, $post);
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

    #[Route('/{postId}', name: 'app_post_delete', methods: ['POST'])]
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
                'username' => 'John Doe',
                'profile_picture' => 'default_profile_pic.jpg',
            ];
        }

        return null; // No user logged in
    }
}