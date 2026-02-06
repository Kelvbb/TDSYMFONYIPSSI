<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(PostRepository $postRepository, UserRepository $userRepository, CommentRepository $commentRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'posts_count' => $postRepository->count([]),
            'users_count' => $userRepository->count([]),
            'comments_count' => $commentRepository->count([]),
        ]);
    }

    // --- Articles ---
    #[Route('/posts', name: 'admin_post_index', methods: ['GET'])]
    public function postIndex(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC']);
        return $this->render('admin/post/index.html.twig', ['posts' => $posts]);
    }

    #[Route('/posts/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function postNew(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $post->setAuthor($this->getUser());
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPublishedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Article créé.');
            return $this->redirectToRoute('admin_post_index');
        }
        return $this->render('admin/post/form.html.twig', ['post' => $post, 'form' => $form]);
    }

    #[Route('/posts/{id}/edit', name: 'admin_post_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function postEdit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Article mis à jour.');
            return $this->redirectToRoute('admin_post_index');
        }
        return $this->render('admin/post/form.html.twig', ['post' => $post, 'form' => $form]);
    }

    #[Route('/posts/{id}/delete', name: 'admin_post_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function postDelete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), (string) $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Article supprimé.');
        }
        return $this->redirectToRoute('admin_post_index');
    }

    // --- Catégories ---
    #[Route('/categories', name: 'admin_category_index', methods: ['GET'])]
    public function categoryIndex(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function categoryNew(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie créée.');
            return $this->redirectToRoute('admin_category_index');
        }
        return $this->render('admin/category/form.html.twig', ['category' => $category, 'form' => $form]);
    }

    #[Route('/categories/{id}/edit', name: 'admin_category_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function categoryEdit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catégorie mise à jour.');
            return $this->redirectToRoute('admin_category_index');
        }
        return $this->render('admin/category/form.html.twig', ['category' => $category, 'form' => $form]);
    }

    #[Route('/categories/{id}/delete', name: 'admin_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function categoryDelete(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-category' . $category->getId(), (string) $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie et articles associés supprimés.');
        }
        return $this->redirectToRoute('admin_category_index');
    }

    // --- Utilisateurs ---
    #[Route('/users', name: 'admin_user_index', methods: ['GET'])]
    public function userIndex(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    #[Route('/users/{id}/toggle-active', name: 'admin_user_toggle_active', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function userToggleActive(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            return $this->redirectToRoute('admin_user_index');
        }
        if ($this->isCsrfTokenValid('toggle-active' . $user->getId(), (string) $request->request->get('_token'))) {
            $user->setIsActive(!$user->isActive());
            $em->flush();
            $this->addFlash('success', $user->isActive() ? 'Compte activé.' : 'Compte désactivé.');
        }
        return $this->redirectToRoute('admin_user_index');
    }

    // --- Commentaires ---
    #[Route('/comments', name: 'admin_comment_index', methods: ['GET'])]
    public function commentIndex(CommentRepository $commentRepository): Response
    {
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $commentRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    #[Route('/comments/{id}/approve', name: 'admin_comment_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function commentApprove(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('approve' . $comment->getId(), (string) $request->request->get('_token'))) {
            $comment->setStatus(Comment::STATUS_APPROVED);
            $em->flush();
            $this->addFlash('success', 'Commentaire approuvé.');
        }
        return $this->redirectToRoute('admin_comment_index');
    }

    #[Route('/comments/{id}/reject', name: 'admin_comment_reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function commentReject(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('reject' . $comment->getId(), (string) $request->request->get('_token'))) {
            $comment->setStatus(Comment::STATUS_REJECTED);
            $em->flush();
            $this->addFlash('success', 'Commentaire désapprouvé.');
        }
        return $this->redirectToRoute('admin_comment_index');
    }
}
