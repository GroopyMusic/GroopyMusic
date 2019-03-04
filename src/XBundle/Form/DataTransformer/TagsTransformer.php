<?php

namespace XBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\Response;
use XBundle\Entity\Tag; 


class TagsTransformer implements DataTransformerInterface
{

	/*
	 * @var ObjectManager
	 */
	private $em;

	public function __construct(ObjectManager $objectManager)
	{
		$this->em = $objectManager;
	}

	public function transform($value){

		return implode(', ', $value); 
	}

	public function reverseTransform($value){

		$names = array_filter(array_unique(array_map('trim', explode(',', $value))));

		$tags = $this->em->getRepository('XBundle:Tag')->findBy([ 'name' => $names]);

		$newTags = array_diff($names, $tags);

		$tag = [];
		foreach ($newTags as $name){
			$tag = new Tag();
			$tag->setName($name);
			$tags[] = $tag;
		}

		return $tags;

	}
	
}