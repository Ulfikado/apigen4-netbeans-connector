# apigen4-netbeans-connector
A wrapper to let access netbeans &lt;= 8.0.2 the ApiGen v4+ tool

## Why is it needed?
ApiGen uses, beginning with version 4.0, a different commandline arguments format. Different from all the version before. THats not fine but happened :-).

But Netbeans does'nt know anything about the differences (currently is version 8.0.2 the latest stable at 2015-04-05)

So errors like:

```
[InvalidArgumentException]                           
There are no commands defined in the "C" namespace.
```

are the result of this "misunderstanding".

I have written this PHP file as a wrapper that converts the old ApiGen comandline args, comming from netbeans or maybe also other software, to the new commandline args supported by ApiGen v4 or higher.

By v4 unsupported (deprecated) arguments will be ignored without some notices.

So, if you dont must/can use ApiGen4 with a tool of youre choise that does not support the new commandline arguments syntax, you are maybee here at the wrong place. But its sure, If you will combine Netbeans v8.0.2 or lower with ApiGen v4.* my little tool can help you to solve the problem.

## Installation
The best way to "install" is:

After you have downloaded or checked out the project, copy the file "apigen4-netbeans-connector.php" to the same location where the apigen.phar ApiGen v4.* file (or symlink to it) is located.

Also copy the batch or shellscript (depending to you're OS "apigen4-netbeans-connector.bat" or "apigen4-netbeans-connector.sh") into same directory.

## Netbeans Configuration

- Goto Menu *Tools* - *Options*
- Select the PHP area
- goto tab *Framework and Tools*
- on the left select "ApiGen"
- insert the path to *ApiGen script* pointing to you're "apigen4-netbeans-connector.(bat|sh)"
 
Thats it.

## Extras

If you use a ApiGen configuration file (*.neon) ApiGen does not understand if the file defines for example a source path,
in relation to the used apigen config file path. If you want use this feature your have to edit the second code line of "apigen4-netbeans-connector.php" and change the line from:

```
$inRelationToConfigFile = false;
```

to

```
$inRelationToConfigFile = true;
```

So the directory of the used config file is set as current working directory and relative paths are used in relation to the config file directory. :-)
