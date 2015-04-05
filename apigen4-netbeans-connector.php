<?php
/*
 * This file defines a wrapping functionality, to access ApiGen v4.* with
 * commandline args format, used by ApiGen versions before 4 and was build to
 * fix the errors while using Netbeans IDE (currently <=8.0.2) with
 * ApiGen 4 or higher. Netbeans currently only knowns Apigen < v4 and does'nt
 * know anything about the new command line arguments format required
 * by ApiGen v4+
 *
 * Installation and usage.
 *
 * First: The following informations are for usage at windows OS. If you use
 * some unixoid systems (Linux, Mac) you have to replace the required Batch
 * files by OS depending shell scripts like *.sh and the required commands.
 *
 * Copy this file to the same folder where apigen.phar (v4.*) is located.
 *
 * Create a new batch file <b>apigen4-netbeans-connector.bat</b> with the
 * following contents
 *
 * <code>
 * @php "%~dp0apigen4-netbeans-connector.php" %*
 * </code>
 *
 * But remember the required php.exe must be inside a folder, defined by
 * current PATH environment variable. Otherwise you must use the absolute
 * path to php.exe
 *
 * It calls the current PHP script inside the actual batch file folder with
 * all defined arguments in old format.
 *
 * In Netbeans: Goto Menu "Tools" - "Options", select the PHP area goto tab
 * "Framework and Tools", on the left select "ApiGen" and insert the path to
 * "ApiGen script" pointing to you're "apigen4-netbeans-connector.bat"
 *
 * @author  Ulf -UlfiKado- Kadner
 * @since   2015-04-05
 * @license LGPL (Lesser Gnu Public License) v2.1
 */


// If you will use relative config paths inside you're apigen.neon in
// relation to the directory that contains the apigen.neon file you have to
// set this value to true. Its only used if the commandline argument
// --config defines a ApiGen configuration file.
$inRelationToConfigFile = false;


/**
 * This function wraps the arguments, passed by Netbeans 8.0.2 or older to the
 * new argument format, required by ApiGen v4+
 *
 * @param  array $args The original arguments passed by netbeans
 * @return string returns the arguments, required by ApiGen v4+ as a ready to use string
 */
function parseArgsFromNetbeans( array $args, &$usedConfigFile )
{

   // Init the required local vars

   // Here the new args are stored
   $newArgs = array();
   // Is a usable arg key like '--config' defined that requires a assoc. value?
   $hasKey  = false;
   // This stores the last open argument key
   $lastKey = '';

   // OK: Now we loop all commandline arguments beginning at element with index 1

   for ( $i = 1; $i < count( $args ); ++$i )
   {

      if ( ! $hasKey )
      {
         // No argument key '--..' is defined
         if ( ! preg_match( '~^--[A-Za-z][A-Za-z0-9-]*$~', $args[ $i ] ) )
         {
            // Does not start with '--' so its a single argument without a value
            $newArgs[] = escapeshellarg( $args[ $i ] );
            // and goto next argument
            continue;
         }
         // Remember the current argument key as last key
         $lastKey = $args[ $i ];
         // Remember the state that we now have a usable last key
         $hasKey  = true;
         // and goto next argument
         continue;
      }

      // Fine: A last key exists, no we require the maybe associated value..

      if ( preg_match( '~^--[A-Za-z][A-Za-z0-9-]*$~', $args[ $i ] ) )
      {
         // ..but the current arg is also a key because its starts with a '--'
         // So we use the last key as a single element without a value
         $newArgs[] = escapeshellarg( $lastKey );
         // Remember the current args as new last key
         $lastKey   = $args[ $i ];
         // and goto next argument
         continue;
      }

      // The current arg is the associated value combine it with the key
      if ( $lastKey == '--update-check' || $lastKey == '--colors' )
      {
         // Ignore unsupported options
         $hasKey    = false;
         $lastKey   = '';
         continue;
      }
      if ( $lastKey == '--source-code' )
      {
         // "--source-code no" will be "--no-source-code".
         if ( strtolower( $args[ $i ] ) == 'no' )
         {
            $newArgs[] = escapeshellarg( '--no-source-code' );
         }
         // "--source-code yes" will be ignored because its the default now
      }
      else if ( preg_match(
         '~^--(internal|debug|deprecated|download|php|todo|tree)$~', $lastKey ) )
      {
         // --internal, --debug, --deprecated, --download, --php,
         // --todo and --tree now does not need a value and will
         // only be triggered if the defined value is 'yes'.
         if ( strtolower( $args[ $i ] ) == 'yes' )
         {
            $newArgs[] = escapeshellarg( $lastKey );
         }
      }
      else if ( $lastKey == '--config' )
      {
         $usedConfigFile = $args[ $i ];
         $newArgs[] = escapeshellarg( $lastKey . '=' . $args[ $i ] );
      }
      else
      {
         $newArgs[] = escapeshellarg( $lastKey . '=' . $args[ $i ] );
      }

      // Remember the state that we now have NO USABLE last key
      $hasKey    = false;
      $lastKey   = '';
      // and goto next argument

   }

   // Dont forget to check if there is a lastKey thats currently without a
   // value and not a part of the resulting $newArgs array
   if ( $hasKey && ! empty( $lastKey ) )
   {
      $newArgs[] = escapeshellarg( $lastKey );
   }

   if ( count( $newArgs ) < 1 )
   {
      return '';
   }

   // Ensure, the first arg must be 'generate'!
   if ( strtolower( $newArgs[ 0 ] ) != 'generate' )
   {
      $newArgs = array_merge( array( 'generate' ), $newArgs );
   }

   // We are done. Return the resulting arguments string
   return implode( ' ', $newArgs );

}


// This variable contains, if config should be read from a *.neon config file,
// after the parseArgsFromNetbeans(...) call, the absolute path of the
// configuration file that should be used.
$usedConfigFile = null;

//Build the required command
$command = 'php "'
          . __DIR__
          . '/apigen.phar" '
          . parseArgsFromNetbeans( $argv, $usedConfigFile );

if ( $inRelationToConfigFile && ! empty( $usedConfigFile ) )
{
   // The configuration is defined by a config file.
   // Setting the config file folder as current working dir
   // So relative config paths will work in relation to the
   // config file directory.
   $dir = dirname( $usedConfigFile );
   chdir( $dir );
}

// Execute the command and direct output the apigen output.
passthru( $command );

// We are done :-)
