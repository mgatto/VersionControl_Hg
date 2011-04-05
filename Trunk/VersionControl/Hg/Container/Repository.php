<?php
/**
 * Contains definition of the Repository class
 *
 * PHP version 5
 *
 * @category    VersionControl
 * @package     Hg
 * @subpackage  Container
 * @author      Michael Gatto <mgatto@lisantra.com>
 * @copyright   2009 Lisantra Technologies, LLC
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     Hg: $Revision$
 * @link 		http://pear.php.net/package/VersionControl_Hg
 */

/**
 * The Mercurial repository
 *
 * Usage:
 * All calls are proxied from Hg
 * <code>
 * $hg = new VersionControl_Hg('/path/to/repo');
 * $repository = $hg->getRepository();
 * </code>
 *
 * PHP version 5
 *
 * @category    VersionControl
 * @package     Hg
 * @subpackage  Container
 * @author      Michael Gatto <mgatto@lisantra.com>
 * @copyright   2009 Lisantra Technologies, LLC
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     Hg: $Revision$
 * @link        http://pear.php.net/package/VersionControl_Hg
 */
class VersionControl_Hg_Container_Repository
{
    /**
     * The name of all Mercurial repository roots.
     *
     * Leading backslash is needed since _path may not have a trailing slash.
     *
     * @const string
     */
    const ROOT_NAME = '.hg';

    /**
     * Path to a local Mercurial repository
     *
     * @var string
     */
    protected $path;

    /**
     *
     * @var string is local, ssh, http??
     */
    protected $transport; //aka $is_remote ?

    /**
     * Repository constructor which currently does nothing.
     *
     * @todo might be a good place to set the transport method?
     */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * Sets the path of a Mercurial repository after validating it as a Hg repo.
     *
     * @param   string $path as a local filesystem path.
     * @return  mixed Repository to enable method chaining
     * @see     $path
     */
    public function setPath($path)
    {
        if (is_array($path)) {
            $path = $path[0];
        }

        //is it even a real path?
        if ( ! realpath($path)) {
            throw new Exception(
                'The path: ' . $path . ' does not exist on this system'
            );
        }

        /*
         * Let's not guess that the user wants to create a repo if none exists;
         * Throw and exception and let them decide what to do next.
         * Maybe they just gave the wrong path.
         *
         * Line breaks are transmitted to CLI apps; concat the strings to
         * ignore them in output.
         */
        if ( ! $this->isRepository($path)) {
            throw new Exception(
                'there is no Mercurial repository at: '
                . $path
                . '. Use $hg->setRepository(\'/path/to/repository\') to create '
                . 'one and then use getRepository() to act upon it.'
            );
        }

        $this->path = $path;

        return $this; //for chainable methods.
    }

    /**
     * Returns the path of a Mercurial repository as set by the user.
     *
     * It is not validated before being set as a class member. This allows
     * it to return null when it needs to and lets the programmer check if a
     * repository has been set or not. Exceptions would remove this control.
     *
     * @return  string | null
     * @see     $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Checks if $this is in fact a valid
     *
     * @param   string $repo is the full repository path.
     * @return  boolean
     */
    public function isRepository($path)
    {
        /*
         * @todo a valid repo has this structure, so test for this:
         * .hg
         *  |---store/
         *        |---data/ (directory)
         *  |---dirstate (file)
         */

        $is_repository = false;

        $repository = $path . DIRECTORY_SEPARATOR . self::ROOT_NAME;

        /*
         * both conditions must be satisfied.
         */
        if (is_dir($repository) && (! empty($repository))) {
            $is_repository = true;
        }

        return $is_repository;
    }


    /**
     *
     *
     * @return
     */
    public function create()
    {
        $this->_command = new VersionControl_Hg_Command_Init();
        //$this->_command = new Hg_Repository_Command_Init($this);
            //pass $this as dependency injection instead of having
            //Hg_Repository_Command inherit from Hg_Repository?


        //return it so we can chain it
        return $this->_command;
    }

    /**
     * Deletes the repository, effectively removing the working copy from SCM.
     *
     * @return boolean
     */
    public function delete()
    {
        if ( unlink(  ) ) {
            return true;
        } else {
            return false;
            //throw new Exception( 'The repository could not be deleted.' );
        }

    }
}