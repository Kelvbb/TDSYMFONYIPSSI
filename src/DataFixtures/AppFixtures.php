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
        // Utilisateurs
        $admin = $this->createUser('admin@paradox.local', 'admin123', 'Admin', 'Paradox', true, User::ROLE_ADMIN, null);
        $manager->persist($admin);

        $usersData = [
            ['marie@example.com', 'user123', 'Marie', 'Dupont', 'https://picsum.photos/id/64/200'],
            ['jean@example.com', 'user123', 'Jean', 'Martin', 'https://picsum.photos/id/65/200'],
            ['sophie@example.com', 'user123', 'Sophie', 'Bernard', 'https://picsum.photos/id/66/200'],
            ['lucas@example.com', 'user123', 'Lucas', 'Petit', null],
            ['lea@example.com', 'user123', 'Léa', 'Durand', null],
        ];
        $users = [$admin];
        foreach ($usersData as [$email, $password, $firstName, $lastName, $profilePicture]) {
            $user = $this->createUser($email, $password, $firstName, $lastName, true, null, $profilePicture);
            $manager->persist($user);
            $users[] = $user;
        }

        // Catégories
        $categoryNames = ['Technologie', 'Voyage', 'Lifestyle', 'Culture', 'Sport'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $cat = new Category();
            $cat->setName($name);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // Articles
        $postsData = [
            ['Introduction à Symfony', 'Symfony est un framework PHP puissant et flexible. Il permet de développer des applications web robustes en suivant les bonnes pratiques. Découvrez ses composants et sa philosophie.', $categories[0], $admin, 'https://picsum.photos/id/1/800/400'],
            ['Mes vacances en Bretagne', 'La Bretagne offre des paysages magnifiques : falaises, plages de sable fin et villages typiques. Un séjour inoubliable entre mer et patrimoine.', $categories[1], $admin, 'https://picsum.photos/id/10/800/400'],
            ['Routine matinale productive', 'Se lever tôt change la vie. Voici mes conseils pour une routine matinale efficace : exercice, méditation et petit-déjeuner équilibré.', $categories[2], $users[1], 'https://picsum.photos/id/100/800/400'],
            ['Les meilleurs livres de l\'été', 'Une sélection de romans et essais à glisser dans votre valise pour les vacances. Des lectures qui marquent les esprits.', $categories[3], $users[2], 'https://picsum.photos/id/101/800/400'],
            ['Course à pied : débuter en douceur', 'Conseils pour bien démarrer la course à pied sans se blesser. Programme progressif sur 8 semaines pour les débutants.', $categories[4], $admin, 'https://picsum.photos/id/102/800/400'],
            ['PHP 8 : les nouvelles fonctionnalités', 'Découvrez les améliorations de PHP 8 : attributes, match expression, union types, named arguments et bien plus encore.', $categories[0], $users[1], 'https://picsum.photos/id/103/800/400'],
            ['Week-end à Amsterdam', 'Balade le long des canaux, musées d\'exception et ambiance unique. Amsterdam en 48 heures, le guide complet.', $categories[1], $users[3], 'https://picsum.photos/id/104/800/400'],
        ];
        $posts = [];
        $dayOffset = 0;
        foreach ($postsData as [$title, $content, $cat, $author, $picture]) {
            $post = new Post();
            $post->setTitle($title);
            $post->setContent($content);
            $post->setCategory($cat);
            $post->setAuthor($author);
            $post->setPublishedAt(new \DateTimeImmutable("-{$dayOffset} days"));
            $post->setPicture($picture);
            $manager->persist($post);
            $posts[] = $post;
            $dayOffset += 2;
        }

        // Commentaires
        $commentsData = [
            [$posts[0], $users[1], 'Excellent article, très instructif !', Comment::STATUS_APPROVED],
            [$posts[0], $users[2], 'Merci pour ce partage, je vais tester Symfony.', Comment::STATUS_APPROVED],
            [$posts[0], $users[3], 'En attente de modération...', Comment::STATUS_PENDING],
            [$posts[1], $users[1], 'J\'ai adoré la Bretagne aussi. Les crêpes !', Comment::STATUS_APPROVED],
            [$posts[1], $users[4], 'Quelle belle région, j\'y retourne cet été.', Comment::STATUS_APPROVED],
            [$posts[2], $users[2], 'La routine matinale a changé ma vie.', Comment::STATUS_APPROVED],
            [$posts[3], $users[1], 'Super sélection, j\'ai noté plusieurs titres.', Comment::STATUS_APPROVED],
            [$posts[3], $users[4], 'Le dernier livre de la liste est un coup de cœur.', Comment::STATUS_PENDING],
            [$posts[4], $users[3], 'Parfait pour reprendre le sport en douceur.', Comment::STATUS_APPROVED],
            [$posts[5], $users[2], 'Les attributes PHP 8 sont géniaux !', Comment::STATUS_APPROVED],
            [$posts[6], $users[1], 'Amsterdam est magique au printemps.', Comment::STATUS_APPROVED],
        ];
        foreach ($commentsData as [$post, $author, $content, $status]) {
            $comment = new Comment();
            $comment->setPost($post);
            $comment->setAuthor($author);
            $comment->setContent($content);
            $comment->setStatus($status);
            $manager->persist($comment);
        }

        $manager->flush();
    }

    private function createUser(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        bool $isActive,
        ?string $role,
        ?string $profilePicture = null
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setIsActive($isActive);
        if ($role) {
            $user->setRoles([$role]);
        }
        if ($profilePicture) {
            $user->setProfilePicture($profilePicture);
        }
        return $user;
    }
}
