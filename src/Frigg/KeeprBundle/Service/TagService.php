<?php

namespace Frigg\KeeprBundle\Service;

use Doctrine\ORM\EntityManager;
use Frigg\KeeprBundle\Entity\User;
use Frigg\KeeprBundle\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class TagService extends ParentServiceAbstract implements UserContainerInterface
{
    protected $finder;
    protected $userService = null;

    public function __construct(EntityManager $em, TransformedFinder $finder, $configFile)
    {
        parent::__construct($em, $configFile);
        $this->finder = $finder;
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function setUserService(UserServiceInterface $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    public function loadEntityBy($attributes)
    {
        $this->entity = $em->getRepository('FriggKeeprBundle:Tag')->findOneBy($attributes);
        return $this;
    }

    public function loadEntityById($id)
    {
        $this->entity = $this->em->getRepository('FriggKeeprBundle:Tag')->findOneById($id);
        return $this;
    }

    public function loadEntityByIdentifier($identifier)
    {
        $this->entity = $this->em->getRepository('FriggKeeprBundle:Tag')->findOneByIdentifier($identifier);
        return $this;
    }

    public function loadAll()
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('t')
            ->from('FriggKeeprBundle:Tag', 't')
            ->orderBy('t.name', 'ASC')
            ->getQuery()->getResult();

        return $this;
    }

    public function loadTagPosts()
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('p')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id = :tag_id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('p.created_at', 'DESC')
            ->setParameters(array(
                'tag_id' => $this->entity->getId(),
                'private' => 1,
                'current_user_id' => $this->getUserService()->getCurrentUserId()
            ))
            ->getQuery()->getResult();

        return $this;
    }


    public function loadPopularTags($limit = null)
    {
        if (!is_object($this->getUserService())) {
            $this->collection = array();
            return $this->collection();
        }

        if ($limit === null) {
            $limit = $this->getConfig('tag_group_limit');
        }

        $qb = $this->em->createQueryBuilder();
        $this->collection = $qb->select('t.id, t.identifier, t.name, COUNT(p.id) AS post_count')
            ->from('FriggKeeprBundle:Post', 'p')
            ->leftJoin('p.Tags', 't')
            ->where('t.id IS NOT NULL')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->neq('p.private', ':private'),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.private', ':private'),
                        $qb->expr()->eq('p.User', ':current_user_id')
                    )
                )
            )
            ->orderBy('post_count', 'DESC')
            ->groupBy('t.identifier')
            ->setMaxResults($limit)
            ->setParameters(array(
                'private' => 1,
                'current_user_id' => $this->getUserService()->getCurrentUserId()
            ))
            ->getQuery()->getResult();

        return $this;
    }

    public function getCloudPecentages()
    {
        $share = array();
        foreach ($this->collection as $item) {
            $share[$item['identifier']] = $item['post_count'];
        }

        $total = array_sum($share);
        $share = array_map(function($hits) use ($total) {
           return round($hits / $total * 100, 1);
        }, $share);

        return $share;
    }
}
