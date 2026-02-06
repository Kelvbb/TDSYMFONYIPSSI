<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findPublishedOrderByDate();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/blog/{id}', name: 'blog_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em): Response
    {
        $post = $postRepository->findWithRelations($id);
        if ($post === null) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        $comment = new Comment();
        $comment->setPost($post);
        if ($this->getUser()) {
            $comment->setAuthor($this->getUser());
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour commenter.');
                return $this->redirectToRoute('app_login');
            }
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Votre commentaire a été enregistré. Il sera visible après modération.');
            return $this->redirectToRoute('blog_show', ['id' => $id]);
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'comment_form' => $form,
        ]);
    }
}
