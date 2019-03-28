<?php

namespace XBundle\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Form\DataTransformer\TagTransformer;

class TagType extends AbstractType
{

    /*
	 * @var ObjectManager
	 */
	private $em;

	public function __construct(ObjectManager $objectManager)
	{
		$this->em = $objectManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagTransformer($this->em), true);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => [
				'data-role' => 'tagsinput'
			]
        ));
    }

    public function getParent()
	{
		return TextType::class;
	}


}
