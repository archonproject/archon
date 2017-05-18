<?php
abstract class Core_QueryLog
{
    public function logQuery(&$DB, $Scope, $Message, $IsManip = NULL)
    {
        $this->QueryCount++;

        if($this->OutputQueries)
        {
            echo("\n\n$Message\n\n");
        }
    }

    public $QueryCount = 0;

    public $OutputQueries = false;
}

$_ARCHON->mixClasses('QueryLog', 'Core_QueryLog');
?>
