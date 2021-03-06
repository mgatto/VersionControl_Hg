<?php
/**
 * Contains the definition of the VersionControl_Hg_Repository_Command_Cat
 * class
 *
 * PHP version 5
 *
 * @category   VersionControl
 * @package    Hg
 * @subpackage Command
 * @author     Michael Gatto <mgatto@lisantra.com>
 * @copyright  2011 Lisantra Technologies, LLC
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       http://pear.php.net/package/VersionControl_Hg
 * @filesource
 */

/**
 * Provides the required interface for all commands
 */
require_once 'Interface.php';

/**
 * Provides base functionality common to all commands
 */
require_once 'Abstract.php';

/**
 * Provides Exceptions for commands (VersionControl_Hg_Command_Exception)
 */
require_once 'Exception.php';

/**
 * Provides an output formatter
 */
require_once 'Output/Formatter.php';

/**
 * Print the contents of a file from a specific revision
 *
 * Usage:
 * <code>
 * $hg = new VersionControl_Hg('/path/to/repo');
 * $hg->cat('/path/to/a/file')->run();
 * </code>
 *
 * You may also specify multiple files:
 * <code>
 * $hg = new VersionControl_Hg('/path/to/repo');
 * $hg->cat()->files(array('file1', 'file2'))->run();
 * </code>
 * or use a pattern:
 * <code>
 * $hg->cat()->files(array('glob'=> '**.php'))->run();
 * </code>
 *
 * Additionaly, you may cat the contents of a file at a specific revision:
 * <code>
 * $hg = new VersionControl_Hg('/path/to/repo');
 * $contents = $hg->cat('file2')->revision(6)->run();
 * file_put_contents('file2', $content);
 * </code>
 * Not specifying a revision causes Mercurial to cat the latest version of the
 * file.
 *
 * As a convenience for the latter operation, a programmer may use the save()
 * method:
 * <code>
 * $hg = new VersionControl_Hg('/path/to/repo');
 * $hg->cat('file2')->revision(6)->save('new_file_name')->to('/path')->run();
 * </code>
 *
 * or, spell out the options in an array:
 * <code>
 * $hg->cat('file2')->save(array('name' => 'new_file_name', 'to' => '/path'))->run();
 * </code>
 *
 * Note: if you really need multiple files, consider using the Archive command.
 *
 * PHP version 5
 *
 * @category   VersionControl
 * @package    Hg
 * @subpackage Command
 * @author     Michael Gatto <mgatto@lisantra.com>
 * @copyright  2011 Lisantra Technologies, LLC
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       http://pear.php.net/package/VersionControl_Hg
 */
class VersionControl_Hg_Command_Cat
    extends VersionControl_Hg_Command_Abstract
        implements VersionControl_Hg_Command_Interface
{
    /**
     * The name of the mercurial command implemented here
     *
     * @var string
     */
    protected $command = 'cat';

    /**
     * Required options for this specific command. These may not be required
     * by Mercurial itself, but are required for the proper functioning of
     * this package.
     *
     * @var mixed
     */
    protected $required_options = array(
        'noninteractive' => null,
        'repository' => null,
        'files' => null,
    );

    /**
     * Permissable options.
     *
     * The actual option must be the key, while 'null' is a value here to
     * accommodate the current implementation of setting options.
     *
     * @var mixed
     */
    protected $allowed_options = array(
        'save' => null,
        'to' => null,
        'revision' => null,
        'rev' => null,
        'output' => null,
        'files' => null,
    );

    /**
     * Constructor
     *
     * @param mixed             $params One or more parameters to modify the command
     * @param VersionControl_Hg $hg     The base Hg instance
     *
     * @return void
     */
    public function __construct($params = null, VersionControl_Hg $hg)
    {
        /* Make $hg available to option methods */
        $this->hg = $hg;

        /* should always be called so we have a full array of valid options */
        $this->setOptions(array());

        /* $params is always an array with key [0] since we use
         * call_user_func_array(). However, if cat() has no arguments,
         * it is an empty array. */
        if ( (! empty($params)) && (isset($params[0])) ) {
            $this->addOption('files', $params[0]);
        }
    }

    /**
     * Execute the command and return the results.
     *
     * @param mixed $params The options passed to the Log command
     *
     * @return string
     */
    public function execute(array $params = null, VersionControl_Hg $hg = null)
    {
        /* take care of options passed into run() as such:
         * $hg->cat('/file/')->run('verbose'));
         * Although, 'verbose|quiet' probably have no effect...?
         */
        if ( ! empty($params) ) {
            $this->setOptions($params);
        }

        /* --noninteractive is required since issuing the command is
         * unattended by nature of using this package.
         *
         * --repository PATH is required since the PWD on which hg is invoked
         * will not be within the working copy of the repo. */
        $this->addOptions(
            array(
                'noninteractive' => null,
                'repository' => $this->hg->getRepository()->getPath(),
                'cwd' => $this->hg->getRepository()->getPath(),
            )
        );

        /* Despite its being so not variable, we need to set the command string
         * only after manually setting options and other command-specific data */
        $this->setCommandString();

        /* no var assignment, since 2nd param holds output */
        exec($this->command_string, $this->output, $this->status);

        if ( $this->status !== 0 ) {
            throw new VersionControl_Hg_Command_Exception(
                VersionControl_Hg_Command_Exception::COMMANDLINE_ERROR
            );
        }

        return VersionControl_Hg_Command_Output_Formatter::toRaw($this->output);
    }

    /**
     * Specified the revision to restrict the cat operation to
     *
     * Usage:
     * <code>$hg->cat('file_name')->revision(7)->run();</code>
     * or
     * <code>$hg->cat(array('revision' => 7 ))->run();</code>
     *
     * @param string $revision is the optional revision to cat
     *
     * @return void
     */
    public function revision($revision = 'tip')
    {
        $this->addOption('rev', $revision);

        /* for the fluent API */
        return $this;
    }

    /**
     * List status information for only the files specified.
     *
     * VersionControl_Hg_Command_Abstract::formatOptions will automatically
     * make this the last option since a files list must be the last item on
     * the command line.
     *
     * Usage:
     * <code>$hg->cat()->files(array('index.php'))->run();</code>
     * or
     * <code>$hg->cat(array('files' => array('index.php')))->run();</code>
     *
     * @param mixed $files the list of files as a simple array
     *
     * @return null
     */
    public function files(array $files)
    {
        /* is it a pattern or a simple array of files?
         * the scheme must be the very first key.
         * Must cast to string since numerical zero seems to always be in an
         * array?! (string '0' is not!) */
        if ( in_array((string) key($files), array('glob','re','set','path','listfile')) ) {
            /* Yup, its a scheme:pattern */
            $filter = $this->parseFilter($files);
        } else {
            $filter = join(' ', $files);
        }

        $this->addOption('files', $filter);
        /* for the fluent API */
        return $this;
    }

    /**
     * set the --output options to designate a file name and directory to
     * where the catted file will be saved
     *
     * You can rename a single cat'ed file with:
     * <code>
     * $hg->cat('file.txt')->save(array('name' => 'new_file.csv'))->run();
     * </code>
     *
     * @param string $name The name of the file name
     *
     * @return VersionControl_Hg_Command_Abstract
     */
    public function save($name = '%s')
    {
        /* Enable this syntax, too: save(array('name' => '', 'to' => '')) */
        if ( is_array($name) ) {
            /* check options separately so we can use them together or
             * on each's own */
            if ( array_key_exists('name', $name) ) {
                $this->addOption('output', $name['name']);
            }

            if ( array_key_exists('to', $name) ) {
                $this->to($name['to']);
            }
        } elseif ( is_scalar($name) ) {
            $this->addOption('output', $name);
        }

        /* for fluent api */
        return $this;
    }

     /**
     * Modifies the --output option to set the directory path to which the
     * catted file will be saved
     *
     * Defaults to the current directory, specified by --cwd and usually is
     * the same as the repository path.
     *
     * @param string $directory is directory to which archives are saved
     *
     * @return VersionControl_Hg_Command_Abstract
     * @throws VersionControl_Hg_Repository_Command_Exception
     */
    public function to($directory = '.')
    {
        /* test path's validity */
        if ( $directory != realpath($directory) ) {
            throw new VersionControl_Hg_Repository_Command_Exception(
                VersionControl_Hg_Command_Exception::BAD_ARGUMENT,
                "The path '{$directory}' does not seem to exist on
                this server. "
            );
        }

        $output_template = $this->getOption('output');

        /* append the directory to --output */
        $output_template = $directory . DIRECTORY_SEPARATOR . $output_template;

        /* now, add it back to the stack of options... */
        $this->options['output'] = $output_template;

        /* for the fluent api */
        return $this;
    }

}
