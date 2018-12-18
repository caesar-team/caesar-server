<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Directory;
use App\Entity\Post;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends AbstractFixture implements DependentFixtureInterface
{
    private const SECRET = '-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 Comment: https://openpgpjs.org wy4ECQMIBYjbgOIFSDvgsZYljrhhM1bOb1z5kAv1YFacvQ99dtDSOa9740+C Ch310pUBJsvSUcBzFVeYJ0vTtvwYRr5VIzJsqMTKom5nA+hmxbcrDGeY9n8p VqV4fxH15vbQzX0/qpMeF2uGejF7MPzBBpBQuVsPg7cZ8z+ivcSfLL5fxDn+ PkPgzp+Em75Y30oPmLr+ceT9qiC63dVEy0xAo0Xz7JIZbyelc4Q0icEDFHTR 26PgZwwrZ51oyuEUsR9crfPrNw== =wPdH -----END PGP MESSAGE----- ';

    public function loadProd(ObjectManager $manager)
    {
        $ipopovList = $this->getRef(Directory::class, 'ipopovlist');
        $dspiridonovList = $this->getRef(Directory::class, 'dspiridonovlist');
        $posts = [];

        $posts['ipopovpost'] = new Post();
        $posts['ipopovpost']->setParentList($ipopovList);
        $posts['ipopovpost']->setSecret(self::SECRET);

        $posts['dspiridonovpost'] = new Post();
        $posts['dspiridonovpost']->setParentList($dspiridonovList);
        $posts['dspiridonovpost']->setSecret(self::SECRET);

        $this->save($posts, Post::class);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
