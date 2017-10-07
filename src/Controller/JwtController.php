<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use ZF\OAuth2\Doctrine\Entity;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

class JwtController extends AbstractActionController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    private $config;
    private $console;

    public function __construct(ObjectManager $objectManager, Console $console, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->console = $console;
        $this->config = $config;
    }

    public function createAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $this->getObjectManager()->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (! $client) {
            $this->console->writeLine("Client not found", Color::RED);
            return;
        }

        // Get the subject
        $subject = Prompt\Line::prompt("The subject, usually a user_id.  Not required: ", true, 255);

        // Get public key path
        $publicKeyPath= '';
        while (!file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        $jwt = new Entity\Jwt;
        $jwt->setClient($client);
        $jwt->setSubject($subject);
        $jwt->setPublicKey($publicKey);

        $this->getObjectManager()->persist($jwt);
        $this->getObjectManager()->flush();

        $this->console->writeLine("JWT created", Color::GREEN);
    }

    public function listAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $jwts = $this->getObjectManager()->getRepository(
            $config['mapping']['Jwt']['entity']
        )->findBy(array(), array('id' => 'ASC'));

        $this->console->writeLine("id\tclient\tclientId\tsubject", Color::YELLOW);
        foreach ($jwts as $jwt) {
            $this->console->writeLine(
                  $jwt->getId()
                . "\t"
                . $jwt->getClient()->getId()
                . "\t"
                . $jwt->getClient()->getClientId()
                . "\t"
                . $jwt->getSubject()
                , Color::CYAN);
        }
    }

    public function deleteAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $jwt = $this->getObjectManager()->getRepository(
            $config['mapping']['Jwt']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (! $jwt) {
            $this->console->writeLine("JWT not found", Color::RED);
            return;
        }

        $this->getObjectManager()->remove($jwt);
        $this->getObjectManager()->flush();

        $this->console->writeLine("JWT deleted", Color::GREEN);
    }
}
