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

#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route('/new/{postId}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $postId): Response
    {
        // Fetch the Post entity based on postId
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }

        // Create a new Comment and associate it with the Post
        $comment = new Comment();
        $comment->setPost($post); // Link the comment to the post

        // Simulate a logged-in user (replace with actual user retrieval logic)
        $simulateUser = false;
        if ($simulateUser) {
            $comment->setUserId(1); // Example user ID
        } else {
            // Replace with actual user retrieval logic
            // $comment->setUserId($this->getUser()->getId());
        }

        // Create the form
        $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 3],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirect to the post's show page
            return $this->redirectToRoute('app_post_show', ['postId' => $post->getPostId()]);
        }

        return $this->render('front_office/comment/new.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/{commentId}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Get the postId from the comment's associated post
        $postId = $comment->getPost()->getPostId();

        $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 3],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect to the post's show page
            return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
        }

        return $this->render('front_office/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{commentId}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Get the postId from the comment's associated post
        $postId = $comment->getPost()->getPostId();

        if ($this->isCsrfTokenValid('delete' . $comment->getCommentId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        // Redirect to the post's show page
        return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
    }
}