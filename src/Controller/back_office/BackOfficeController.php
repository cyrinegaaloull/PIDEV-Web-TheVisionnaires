<?php

namespace App\Controller\back_office;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('back_office/dashboard.html.twig');
    }

    #[Route('/admin/flux-social', name: 'admin_flux_social')]
    public function fluxSocial(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render('back_office/flux_social.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * Post Details Page
     */
    #[Route('/admin/post/{postId}/details', name: 'admin_post_details')]
    public function postDetails(int $postId, EntityManagerInterface $entityManager): Response
    {
        // Fetch the post by ID
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Post not found.');
        }

        // Fetch all comments for the post
        $comments = $post->getComments();

        return $this->render('back_office/post_details.html.twig', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * Delete Post
     */
    #[Route('/admin/post/{postId}/delete', name: 'admin_post_delete', methods: ['POST'])]
    public function deletePost(int $postId, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Fetch the post by ID
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Post not found.');
        }

        // Validate CSRF token
        if ($this->isCsrfTokenValid('delete-post-' . $postId, $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        // Redirect back to the Flux Social page
        return $this->redirectToRoute('admin_flux_social');
    }

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