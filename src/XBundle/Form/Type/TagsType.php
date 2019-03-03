<?php 

namespace XBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Form\ImageType;
use XBundle\Form\DataTransformer\TagsTransformer;

class TagsType extends AbstractType 
{
	/*
	 * @var ObjectManager
	 */
	private $em;

	public function __construct(ObjectManager $objectManager)
	{
		$this->em = $objectManager;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->addModelTransformer(new CollectionToArrayTransformer(), true)->addModelTransformer(new TagsTransformer($this->em), true);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'attr' => [
				'data-role' => 'tagsinput'
			]
		]);
	}

	public function getParent()
	{
		return TextType::class;
	}

}

?>