<?php

namespace App\Controller\front_office;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route('/new/{postId}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $postId): Response
    {
        // Fetch the Post entity based on postId
        $post = $entityManager->getRepository(\App\Entity\Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }

        // Simulate a logged-in user (replace with actual user retrieval logic)
        $simulateUser = false;
        $user = $this->getSimulatedUser($simulateUser);

        // Create a new Comment and associate it with the Post
        $comment = new Comment();
        $comment->setPost($post); // Link the comment to the post

        if ($user) {
            $comment->setUserId($user['id']); // Example user ID
        } else {
            $comment->setUserId(1); // Default user ID for simulation
        }

        // Handle form submission
        $form = $this->createFormBuilder($comment)
            ->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Écrivez votre commentaire ici...',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['postId' => $post->getPostId()]);
        }

        return $this->render('front_office/comment/new.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'user' => $user, // Pass the user to the template
        ]);
    }

    #[Route('/{commentId}/edit-form', name: 'app_comment_edit_form', methods: ['POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Simulate a logged-in user (for testing purposes)
        $simulateUser = true; // Enable simulation
        $user = $this->getSimulatedUser($simulateUser);

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('edit-comment' . $comment->getCommentId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Update the comment content
        $newContent = trim($request->get('content'));
        if (empty($newContent)) {
            $this->addFlash('error', 'Le contenu du commentaire ne peut pas être vide.');
        } else {
            $comment->setContent($newContent);
            $entityManager->flush();
            $this->addFlash('success', 'Le commentaire a été mis à jour avec succès.');
        }

        // Redirect back to the post's show page
        return $this->redirectToRoute('app_post_show', ['postId' => $comment->getPost()->getPostId()]);
    }

    #[Route('/{commentId}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('delete-comment' . $comment->getCommentId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Delete the comment
        $postId = $comment->getPost()->getPostId();
        $entityManager->remove($comment);
        $entityManager->flush();

        // Redirect back to the post's show page
        return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
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