<?php

namespace App\Controller\back_office;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * Delete Comment
     */
    #[Route('/admin/comment/{commentId}/delete', name: 'admin_comment_delete', methods: ['POST'])]
    public function deleteComment(int $commentId, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Fetch the comment by ID
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found.');
        }

        // Validate CSRF token
        if ($this->isCsrfTokenValid('delete-comment-' . $commentId, $request->request->get('_token'))) {
            $postId = $comment->getPost()->getPostId(); // Get the associated post ID
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        // Redirect back to the post details page
        return $this->redirectToRoute('admin_post_details', ['postId' => $postId]);
    }
}