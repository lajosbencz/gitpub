<?php
/**
 * Script for bumping semantic versioned packages to git remote
 *
 * @version 0.1.0
 * @author Lajos Bencz <lazos@lazos.me>
 * @license MIT
 */

if (!is_dir('.git')) {
    die('Not a git repository:' . PHP_EOL . getcwd());
}

$diff = trim(`git diff`);
if (strlen($diff) > 0) {
    $commitMessage = readline('Commit message: ');
    echo 'Committing: ', $commitMessage, PHP_EOL;
    echo `git commit -a -m "{$commitMessage}"`;
} else {
    echo 'Nothing to commit', PHP_EOL;
}

$highestTag = [0, 0, 0];
$tags = array_map(function ($i) {
    return trim($i);
}, explode("\n", `git tag`));
foreach ($tags as $tag) {
    if (preg_match('/([\d]+)[^\d]+([\d]+)[^\d]+([\d]+)/', $tag, $match)) {
        if ($match[1] > $highestTag[0]) {
            $highestTag[0] = intval($match[1]);
            $highestTag[1] = intval($match[2]);
            $highestTag[2] = intval($match[3]);
        } elseif ($match[1] == $highestTag[0] && $match[2] > $highestTag[1]) {
            $highestTag[1] = intval($match[2]);
            $highestTag[2] = intval($match[3]);
        } elseif ($match[1] == $highestTag[0] && $match[2] == $highestTag[1] && $match[3] > $highestTag[2]) {
            $highestTag[2] = intval($match[3]);
        }
    }
}

$tagIndex = 2;
if ($argc > 1) {
    switch ($argv[1]) {
        default:
        case 'patch':
            $tagIndex = 2;
            break;
        case 'minor':
            $tagIndex = 1;
            break;
        case 'major':
            $tagIndex = 0;
            break;
    }
}

if ($tagIndex == 0) {
    $highestTag[1] = 0;
    $highestTag[2] = 0;
} elseif ($tagIndex == 1) {
    $highestTag[2] = 0;
}

$highestTag[$tagIndex]++;
$newTag = 'v' . $highestTag[0] . '.' . $highestTag[1] . '.' . $highestTag[2];

echo 'New tag: ', $newTag, PHP_EOL;

echo `git tag {$newTag}`;
echo `git push --tags`;
echo `git push --all`;
