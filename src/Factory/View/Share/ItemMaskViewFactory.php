<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: mg
 * Date: 3/12/19
 * Time: 7:51 PM
 */

namespace App\Factory\View\Share;

use App\Entity\ItemMask;
use App\Model\View\Share\ItemMasksView;
use App\Model\View\Share\ItemMaskView;
use App\Model\View\User\UserView;
use Doctrine\Common\Collections\ArrayCollection;

class ItemMaskViewFactory
{
    /**
     * @param ItemMask[]|ArrayCollection $itemMasks
     * @return ItemMasksView
     */
    public function create($itemMasks): ItemMasksView
    {
        $itemMasksView = new ItemMasksView();
        foreach ($itemMasks as $itemMask) {
            $itemMaskView = $this->createMaskView($itemMask);
            $itemMasksView->addItemMask($itemMaskView);
        }

        return $itemMasksView;
    }

    public function createOne(ItemMask $itemMask): ItemMaskView
    {
        return $this->createMaskView($itemMask);
    }

    private function createMaskView(ItemMask $itemMask): ItemMaskView
    {
        $itemMaskView = new ItemMaskView();
        $itemMaskView->id = $itemMask->getId()->toString();
        $itemMaskView->originalItem = $itemMask->getOriginalItem()->getId()->toString();
        $itemMaskView->secret = $itemMask->getSecret();
        $itemMaskView->access = $itemMask->getAccess();
        $recipient = $itemMask->getRecipient();
        $recipientView = new UserView();
        $recipientView->id = $recipient->getId()->toString();
        $recipientView->name = $recipient->getUsername();
        $recipientView->avatar = null === $recipient->getAvatar() ? null : $recipient->getAvatar()->getLink();
        $recipientView->publicKey = $recipient->getPublicKey();
        $recipientView->email = $recipient->getEmail();
        $itemMaskView->recipient = $recipientView;

        return $itemMaskView;
    }
}