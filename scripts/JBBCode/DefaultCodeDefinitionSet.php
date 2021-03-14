<?php
namespace JBBCode;

require_once 'CodeDefinition.php';
require_once 'CodeDefinitionBuilder.php';
require_once 'CodeDefinitionSet.php';
require_once 'validators/CssColorValidator.php';
require_once 'validators/UrlValidator.php';

/**
 * Provides a default set of common bbcode definitions.
 *
 * @author jbowens
 */
class DefaultCodeDefinitionSet implements CodeDefinitionSet
{

	/* The default code definitions in this set. */
	protected $definitions = array();

	/**
	 * Constructs the default code definitions.
	 */
	public function __construct()
	{
		/* [b] bold tag */
		$builder = new CodeDefinitionBuilder('b', '<strong>{param}</strong>');
		array_push($this->definitions, $builder->build());

		/* [i] italics tag */
		$builder = new CodeDefinitionBuilder('i', '<em>{param}</em>');
		array_push($this->definitions, $builder->build());

		/* [u] underline tag */
		$builder = new CodeDefinitionBuilder('u', '<u>{param}</u>');
		array_push($this->definitions, $builder->build());

		/* [s] underline tag */
		$builder = new CodeDefinitionBuilder('s', '<s>{param}</s>');
		array_push($this->definitions, $builder->build());

		/* [h] underline tag */
		$builder = new CodeDefinitionBuilder('h', '<mark>{param}</mark>');
		array_push($this->definitions, $builder->build());

		/* [sup] underline tag */
		$builder = new CodeDefinitionBuilder('sup', '<sup>{param}</sup>');
		array_push($this->definitions, $builder->build());

		/* [sub] underline tag */
		$builder = new CodeDefinitionBuilder('sub', '<sub>{param}</sub>');
		array_push($this->definitions, $builder->build());

		/* [code] underline tag */
		$builder = new CodeDefinitionBuilder('code', '<code>{param}</code>');
		array_push($this->definitions, $builder->build());

		/* [hr] underline tag */
		$builder = new CodeDefinitionBuilder('hr', "<hr />{param}</hr>");
		array_push($this->definitions, $builder->build());




		/* [left] center tag */
		$builder = new CodeDefinitionBuilder('left', '<div class="text-left">{param}</div>');
		array_push($this->definitions, $builder->build());

		/* [center] center tag */
		$builder = new CodeDefinitionBuilder('center', '<div class="text-center">{param}</div>');
		array_push($this->definitions, $builder->build());

		/* [right] center tag */
		$builder = new CodeDefinitionBuilder('right', '<div class="text-right">{param}</div>');
		array_push($this->definitions, $builder->build());

		/* [justify] center tag */
		$builder = new CodeDefinitionBuilder('justify', '<div class="text-justify">{param}</div>');
		array_push($this->definitions, $builder->build());



		/* [list] list tag */
		$builder = new CodeDefinitionBuilder('list', '<ul>{param}</ul>');
		array_push($this->definitions, $builder->build());

		/* [ul] list tag */
		$builder = new CodeDefinitionBuilder('ul', '<ul>{param}</ul>');
		array_push($this->definitions, $builder->build());

		/* [ol] list tag */
		$builder = new CodeDefinitionBuilder('ol', '<ol>{param}</ol>');
		array_push($this->definitions, $builder->build());

		/* [*] item tag */
		$builder = new CodeDefinitionBuilder('*', '<li>{param}</li>');
		array_push($this->definitions, $builder->build());

		/* [li] item tag */
		$builder = new CodeDefinitionBuilder('li', '<li>{param}</li>');
		array_push($this->definitions, $builder->build());



		/* [h1] item tag */
		$builder = new CodeDefinitionBuilder('h1', '<h1>{param}</h1>');
		array_push($this->definitions, $builder->build());

		/* [h2] item tag */
		$builder = new CodeDefinitionBuilder('h2', '<h2>{param}</h2>');
		array_push($this->definitions, $builder->build());

		/* [h3] item tag */
		$builder = new CodeDefinitionBuilder('h3', '<h3>{param}</h3>');
		array_push($this->definitions, $builder->build());

		/* [h4] item tag */
		$builder = new CodeDefinitionBuilder('h4', '<h4>{param}</h4>');
		array_push($this->definitions, $builder->build());


		/* [spoiler] spoiler tag */
		$builder = new CodeDefinitionBuilder("spoiler", "<button type='button' class='btn btn-sm btn-warning btn-spoiler'>Spoiler</button><div class='spoiler display-none'>{param}</div>");
		array_push($this->definitions, $builder->build());

		/* [quote] quote tag */
		$builder = new CodeDefinitionBuilder("quote", "<div style='width: 100%; display: inline-block; margin: 1em 0;' class='well well-sm'>{param}</div>");
		array_push($this->definitions, $builder->build());


		$urlValidator = new \JBBCode\validators\UrlValidator();

		/* [url] link tag */
		$builder = new CodeDefinitionBuilder('url', '<a href="{param}" target="_blank">{param}</a>');
		$builder->setParseContent(false)->setBodyValidator($urlValidator);
		array_push($this->definitions, $builder->build());

		/* [url=http://example.com] link tag */
		$builder = new CodeDefinitionBuilder('url', '<a href="{option}" target="_blank">{param}</a>');
		$builder->setUseOption(true)->setParseContent(true)->setOptionValidator($urlValidator);
		array_push($this->definitions, $builder->build());

		/* [img] image tag */
		$builder = new CodeDefinitionBuilder('img', '<img class="align-bottom" src="{param}" style="max-width: 100%; " />');
		$builder->setUseOption(false)->setParseContent(false)->setBodyValidator($urlValidator);
		array_push($this->definitions, $builder->build());

		/* [img=alt text] image tag */
		$builder = new CodeDefinitionBuilder('img', '<img class="align-bottom" src="{param}" alt="{option}" style="max-width: 100%; " />');
		$builder->setUseOption(true)->setParseContent(false)->setBodyValidator($urlValidator);
		array_push($this->definitions, $builder->build());

		/* [color] color tag */
		//$builder = new CodeDefinitionBuilder('color', '<span style="color: {option}">{param}</span>');
		//$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\CssColorValidator());
		//array_push($this->definitions, $builder->build());
	}

	/**
	 * Returns an array of the default code definitions.
	 */
	public function getCodeDefinitions()
	{
		return $this->definitions;
	}

}