<?php

namespace AppBundle\Command;

use AppBundle\Entity\Newsletter;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterCommand extends ContainerAwareCommand
{
    const NB_DAYS_FOR_NEWSLETTER = 13;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newsletter:send')
            ->setDescription('Send the newsletter to all registered users')
            // TODO ->addArgument('contracts-limit')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO $contracts_limit = intval($input->getArgument('contracts-limit'));

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $new_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findBy(array('newsletter' => null));
        $last_newsletter = $em->getRepository('AppBundle:Newsletter')->findOneBy(array(), array('date' => 'desc'));

        if(count($new_crowdfundings) > 0 && ($last_newsletter == null || (new \DateTime())->diff($last_newsletter->getDate())->days > self::NB_DAYS_FOR_NEWSLETTER)) {
            $newsletter = new Newsletter();
            $newsletter->setTitle('Newsletter du ' . $newsletter->getDate()->format('d/m/Y'));

            foreach($new_crowdfundings as $nc) {
                $newsletter->addContract($nc);
            }

            $recipients = array_map(function($elem) {
                return $elem->getEmail();
            }, $em->getRepository('AppBundle:User')->findBy(array('newsletter' => true)));


            $mailer = $this->getContainer()->get('azine_email_template_twig_swift_mailer');

            $from = $this->getContainer()->getParameter('email_from_address');
            $fromName = $this->getContainer()->getParameter('email_from_name');

            $bcc = $recipients;
            $subject = "subject";

            $params = ['newsletter' => $newsletter];

            $mailer->sendEmail($failedRecipients, $subject, $from, $fromName, array(), '', array(), '',
                $bcc, '', array(), '', $params, MailTemplateProvider::NEWSLETTER_TEMPLATE);

            $em->persist($newsletter);

            $em->flush();
        }


    }
}
