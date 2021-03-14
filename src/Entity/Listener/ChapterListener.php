<?php
/**
 * Created by PhpStorm.
 * User: janit
 * Date: 05/12/2018
 * Time: 19:59
 */

namespace Mangadex\Entity\Listener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Mangadex\Entity\Chapter;

class ChapterListener
{
    public function postPersist(Chapter $chapter, LifecycleEventArgs $event)
    {
        
    }

}