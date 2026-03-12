<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for git workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Git
{

/**
 * Retrieve git state through `getCurrentCommit`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getCurrentCommit.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getCurrentCommit()
 * @example /fr/git/getCurrentCommit
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getCurrentCommit(){
        $commit=array();
        $commit['build'] = trim(shell_exec("git rev-parse HEAD"));
        $commit['date'] = trim(shell_exec('git log -1 --format=%cd --date=format:"%Y-%m-%d %H:%M:%S"'));
        $commit['comment'] = trim(shell_exec('git log -1 --pretty=%B'));

        $version_mineur = trim(shell_exec('git rev-list $(git describe --tags `git rev-list --tags --max-count=1`)..HEAD --count'));
        $version_majeur = trim(shell_exec('git describe --tags `git rev-list --tags --max-count=1`'));

        $elems = explode(".",$version_majeur);

        if (count($elems) === 3) {
            $elems[2] = $version_mineur;
        }

        $version = implode(".", $elems);
        $commit['version'] = $version;

        return $commit;
    }

/**
 * Retrieve git state through `getNewCommit`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $build Input value for `build`.
 * @phpstan-param mixed $build
 * @psalm-param mixed $build
 * @return mixed Returned value for getNewCommit.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getNewCommit()
 * @example /fr/git/getNewCommit
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getNewCommit($build){
        
        
        $commits = shell_exec('git log '.$build.'..HEAD --pretty=format:"%H"');

        if (!empty($commits)){
            $commits = trim($commits);
        }
        else{
            $commits = "";
        }
        
        $commits_following = explode("\n", $commits);
        array_unshift($commits_following, $build);

        return $commits_following;
    }


}

/*



        $build = 
        $date = 
        $comment = 
*/
