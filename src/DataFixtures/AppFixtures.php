<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@paradox.local');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setFirstName('Admin');
        $admin->setLastName('Paradox');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setIsActive(true);
        $manager->persist($admin);

        // Utilisateur
        $user = new User();
        $user->setEmail('marie@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setFirstName('Marie');
        $user->setLastName('Dupont');
        $user->setProfilePicture('https://picsum.photos/id/64/200');
        $user->setIsActive(true);
        $manager->persist($user);

        // Catégories
        $categories = ['Technologie', 'Voyage', 'Lifestyle'];
        $categoryEntities = [];
        foreach ($categories as $name) {
            $cat = new Category();
            $cat->setName($name);
            $manager->persist($cat);
            $categoryEntities[] = $cat;
        }

        // Articles
        $postsData = [
            ['Introduction à Symfony', 'Symfony est un framework PHP puissant...', $categoryEntities[0], 'https://picsum.photos/id/1/800/400'],
            ['Mes vacances en Bretagne', 'La Bretagne offre des paysages magnifiques...', $categoryEntities[1], 'https://picsum.photos/id/10/800/400'],
            ['Routine matinale productive', 'Se lever tôt change la vie...', $categoryEntities[2], 'https://picsum.photos/id/100/800/400'],
        ];
        $posts = [];
        foreach ($postsData as [$title, $content, $cat, $picture]) {
            $post = new Post();
            $post->setTitle($title);
            $post->setContent($content);
            $post->setCategory($cat);
            $post->setAuthor($admin);
            $post->setPublishedAt(new \DateTimeImmutable('-2 days'));
            $post->setPicture($picture);
            $manager->persist($post);
            $posts[] = $post;
        }

        // Commentaires
        $comment1 = new Comment();
        $comment1->setContent('Excellent article, très instructif !');
        $comment1->setAuthor($user);
        $comment1->setPost($posts[0]);
        $comment1->setStatus(Comment::STATUS_APPROVED);
        $manager->persist($comment1);

        $comment2 = new Comment();
        $comment2->setContent('J\'ai adoré la Bretagne aussi.');
        $comment2->setAuthor($user);
        $comment2->setPost($posts[1]);
        $comment2->setStatus(Comment::STATUS_APPROVED);
        $manager->persist($comment2);

        $comment3 = new Comment();
        $comment3->setContent('En attente de modération...');
        $comment3->setAuthor($user);
        $comment3->setPost($posts[0]);
        $comment3->setStatus(Comment::STATUS_PENDING);
        $manager->persist($comment3);

        $manager->flush();
    }
}
