<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\QueryBuilder;


/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Admin filter: display teachers users
     *
     * @param UserRepository $userRepository
     * @return QueryBuilder
     */
    public static function adminQueryBuilderForTeacher(UserRepository $userRepository): QueryBuilder
    {
        $role = "ROLE_TEACHER";
        $queryBuilder = $userRepository->createQueryBuilder('user');
        $queryBuilder->select('user')
                     ->where('user.roles LIKE :roles')
                     ->setParameter('roles', '%"'.$role.'"%');
        return $queryBuilder;
    }

    /**
     * Admin filter: display students users
     *
     * @param UserRepository $userRepository
     * @return QueryBuilder
     */
    public static function adminQueryBuilderForStudent(UserRepository $userRepository): QueryBuilder
    {
        $role = "ROLE_STUDENT";
        $queryBuilder = $userRepository->createQueryBuilder('user');
        $queryBuilder->select('user')
                     ->where('user.roles LIKE :roles')
                     ->setParameter('roles', '%"'.$role.'"%');
        return $queryBuilder;
    }
}
