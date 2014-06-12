<?PHP
//include_once("LabelText.php");

class LabelBack extends LabelText
{
    protected function getLink()
    {
        return "javascript:history.go(-1);";
    }

}

?>