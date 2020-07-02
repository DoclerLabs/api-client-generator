<?php declare(strict_types=1);

$composer = json_decode(file_get_contents('/app/composer.json'), true);

$option = $argv[1] ?? null;

if ('--name' === $option)
{
	getName($composer);
	exit(0);
}

if ('--namespace' === $option)
{
	getNamespace($composer);
	exit(0);
}

printf("Invalid option: %s\n", $option);
exit(1);

function getName(array $composer): void
{
	if (false === array_key_exists('name', $composer))
	{
		printf("Package name not found on API's composer.json");
		exit(1);
	}

	echo $composer['name'] . '-client';
}

function getNamespace(array $composer): void
{
	$psr4 = $composer['autoload']['psr-4'];
	if (count($psr4) !== 1)
	{
		printf("Can't get the namespace from your composer.json");
		exit(1);
	}
	echo trim(array_keys($psr4)[0], ' \\') . 'Client';
}
