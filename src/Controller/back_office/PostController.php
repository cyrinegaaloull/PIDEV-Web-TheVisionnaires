<?php

namespace App\Controller\back_office;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
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
}