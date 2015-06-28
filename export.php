<?php
/**
*   Simple TSV to SQL export
*/
class TSVtoSQL
{

    private $tsv;

    function __construct($inputFile =''){

        // Read the TSV File and Parse it
        $this->readTsvFile($inputFile);

        // Write the SQL file
        $this->writeSqlFile($inputFile);

    }

    function readTsvFile($inputFile){

        // Check the file exists
        if(!file_exists($inputFile)){
            die("Bad Filename: Maybe check the file's location.\n<br>");
        }

        $this->tsv = new stdClass();

        // Read the files content
        $this->tsv->content = file_get_contents($inputFile);

        // Check its not empty
        if(empty($this->tsv->content)){
            die('Your file has no content in it!');
        }

        // Split the file into lines
        $this->tsv->lines = explode("\n", $this->tsv->content);
        // Remove the blank line
        unset($this->tsv->lines[count($this->tsv->lines)-1]);
        // Find out how many lines we have
        $this->tsv->lineCount = count($this->tsv->lines);
        if(count($this->tsv->lines) < 2){
            die('Your file should contain atleast 2 lines; column names, and 1 row of data');
        }
    }

    function writeSqlFile($inputFile){

        $sqlContent  = "CREATE TABLE IF NOT EXISTS `tsv` (\n";
        $sqlContent .= " `_id` int(11) NOT NULL AUTO_INCREMENT,\n";

        // Add the table column names to the structure
        $columns = explode("\t", $this->tsv->lines[0]);
        $insertColumnNames ='';
        foreach ($columns as $column) {
            // Strip the newlines (really only needed for the last column, but meh)
            $column = trim(preg_replace('/\s+/', ' ', $column));
            $sqlContent .= " `".addslashes($column)."` text NOT NULL,\n";

            $insertColumnNames .= "`".addslashes($column)."`,";
        }
        $sqlContent .= "  PRIMARY KEY (`_id`)\n) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=".$this->tsv->lineCount." ;";


        // Add table columns to the insert values
        $sqlContent .= "\n\nINSERT INTO `tsv` (";
        $sqlContent .= substr($insertColumnNames, 0, -1).") VALUES(\n";


        // Add table data
        for ($i=1; $i < $this->tsv->lineCount; $i++) {

            $columns = explode("\t", $this->tsv->lines[$i]);

            foreach ($columns as $column) {
                $column = trim(preg_replace('/\s+/', ' ', $column));
                $sqlContent .= "`".addslashes($column)."`,";
            }
            $sqlContent .="\n";
        }
        // Clean the last table record
        $sqlContent = substr($sqlContent, 0, -2).");";


        echo 'Woohoo! - All done. Sql store in '.$inputFile.'.sql'."\n<br>";

        file_put_contents($inputFile.'.sql', $sqlContent);
    }

}


// Just change the filename.
$export = new TSVtoSQL('test.tsv');
