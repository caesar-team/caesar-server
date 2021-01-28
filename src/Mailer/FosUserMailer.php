<?php

declare(strict_types=1);

namespace App\Mailer;

use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class FosUserMailer extends Mailer implements MailerInterface
{
    public const FROM_EMAIL = 'no-reply@caesar.team';

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating)
    {
        parent::__construct($mailer, $router, $templating, []);
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_registration_confirm', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $rendered = $this->templating->render('email/confirmation.html.twig', [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);

        $senderAddress = getenv('SENDER_ADDRESS') ?: self::FROM_EMAIL;
        $this->sendEmailMessage($rendered, $senderAddress, (string) $user->getEmail());
    }

    /**
     * Send an email to a user to confirm the password reset.
     */
    public function sendResettingEmailMessage(UserInterface $user): void
    {
        $webClientUrl = getenv('WEB_CLIENT_URL')
            ?: $this->router->generate('root', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $url = $webClientUrl.'resetting/'.$user->getEmail().'/'.strval($user->getConfirmationToken());

        $rendered = $this->templating->render('email/password_resetting.email.html.twig', [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);

        $senderAddress = getenv('SENDER_ADDRESS') ?: self::FROM_EMAIL;
        $this->sendEmailMessage($rendered, $senderAddress, (string) $user->getEmail());
    }

    /**
     * @param string       $renderedTemplate
     * @param array|string $fromEmail
     * @param array|string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = array_shift($renderedLines);
        $body = implode("\n", $renderedLines);

        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');

        if (!$this->mailer->getTransport()->ping()) {
            $this->mailer->getTransport()->stop();
            $this->mailer->getTransport()->start();
        }

        $this->mailer->send($message);
    }
}
