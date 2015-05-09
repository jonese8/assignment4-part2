<?php
#index.php - CS290, Emmalee Jones, Assignment 4.2
#Error Reporting Settings
error_reporting(E_ALL);
ini_set('display_errors', 'Off');

include "secret.php";
include "functions.php";

#Connet To Database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "jonese8-db", $password, 
"jonese8-db");

if ($mysqli->connect_errno) {
    echo "Error: Database connection error: " . $mysqli->connect_errno . " - "
    . $mysqli->connect_error;
}

#Check for various POST Buttons on Form and valid data
if ($_POST) {

    #Test for deleting on video store row
    if (isset($_POST['delete'])) {
        $id = $_POST ['delete'];
        delRow($id, $mysqli);
    }

    #If Delete All Videos Button selection call truncate function
    if (isset($_POST['delAll'])) {
        clearVideos($mysqli);
    }

    #Test for $_POST check in and check out 
    if (isset($_POST['chkInOut'])) {
        $id = $_POST['chkInOut'];
        chkInOut($id, $mysqli);
    }

    #Initialize valid data edit flag
    $passedEdits = TRUE;

    #Test POST and data validation for add video
    #Test POST and valid name
    if (isset($_POST ['name']) && ($_POST ['name'] != NULL)) {
        $formName = $_POST ['name'];

        #Test for Unique Name
        if (isNameUniq($formName, $mysqli) == 0) {

            #Test POST and valid category, if blank set to NULL
            if ((isset($_POST ['category'])) && ($_POST ['category'] != NULL)) {
                $formCat = $_POST ['category'];
            } else {
                $formCat = NULL;
            }

            #Test POST and valid length 
            if ((isset($_POST ['length']) && ($_POST ['length'] != NULL))) {

                #Check for integer
                if ((string) (int) $_POST ['length'] === (string) $_POST 
                    ['length']) {

                    #Check for positive length
                    if ((int) $_POST ['length'] >= 0) {
                        $formLength = $_POST ['length'];
                    } else {
                        echo "<p>Error: Length must be positive.</p>";
                        $passedEdits = FALSE;
                    }
                } else {
                    echo "<p>Error: Video length must be an integer.</p>";
                    $passedEdits = FALSE;
                }
            }
            #If Category empty set to NULL   
            else {
                $formLength = NULL;
            }

            #Passed Edits and store row in database
            if ($passedEdits === TRUE) {

                if (!($stmt = $mysqli->prepare("INSERT INTO video_store"
                    . "(name,category,length) VALUES (?,?,?)"))) {
                    echo "Error: Prepare failed: (" . $mysqli->errno . ") " 
                            . $mysqli->error;
                }

                if (!$stmt->bind_param("ssi", $formName, $formCat, 
                    $formLength)) {
                    echo "Error: Binding parameters failed: (" . $stmt->errno 
                        . ") " . $stmt->error;
                }

                if (!$stmt->execute()) {
                    echo "Error: Execute failed: (" . $stmt->errno . ") " 
                        . $stmt->error;
                }
                $stmt->close();
            }
        }
        #Name was already stored in database
        else {
            echo "<p>Error: Video name must be unique.</p>\n";
        }
    }
    #Name field was left empty, it is a required field
    elseif ((isset($_POST ['category']) || (isset($_POST ['length'])))) {
        echo "<p>Error: Name is a required field.</p>\n";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CS290 Assignment 4.2 - Video Library</title>
        <link rel="stylesheet" href="style.css" type="text/css" media="screen">
    </head>
    <body>
        <form action="index.php" method="POST" name="addForm">
            <h3>Video Library</h3>
            <label>Name: <input type="text" name="name" ></label>
            <label>Category: <input type="text" name="category">  
            </label> <label>Length (in minutes): <input type="text" 
                name="length"></label>
            <input type="submit" value="Add Video"> <br>
            <br/>
        </form>
        <form action="index.php" method="POST" name="tableFunc">
            <button type="submit" name="delAll" value="delVideos">Delete All
                Videos</button>
        </form>
        <br/>
        <form action="index.php" method="POST" name="tableFilter">
            <select name="listCategory"> 

<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off');

#Build drop down of categories when rows available iwth mysqli prepare
if (!($catList = $mysqli->prepare("SELECT DISTINCT category FROM video_store "
    . "ORDER BY category"))) {
    echo "Error: Failed prepare: (" . $mysqli->errno . ") " . $mysqli->error;
}

$formCat = NULL;

if (!$catList->bind_result($formCat)) {
    echo "Error: Failed Bind: (" . $catList->errno . ") " . $catList->error;
}

if (!$catList->execute()) {
    echo "Error: Failed execute: (" . $catList->errno . ") " . $catList->error;
}

#Store the results of the prepared statement
$catList->store_result();
#Load Category List if rows available
if ($catList->num_rows() > 0) {
    echo "\t\t\t<option selected value=\"allCategories\">All Categories"
    . "</option>\n";
    
    while ($catList->fetch()) {
        #Skip NULL category  
        if ($formCat != NULL) {
            echo "\t\t\t<option value=\"{$formCat}\">{$formCat}</option>\n";
        }
    }
}
#Close Fetch of $catList
$catList->close();

?>

            </select> <input type="submit" value="Filter">
        </form>

                <?php
                error_reporting(E_ALL);
                ini_set('display_errors', 'Off');
                
                #Build video listing table, filter if category selection made
                $tableList = "SELECT id, name, category, length, rented FROM"
                    . " video_store";
                if (isset($_POST ['listCategory']) && ($_POST ['listCategory']
                    !== "allCategories")) {
                    $tableList .= " WHERE category=\"" . $_POST ['listCategory']
                        . "\"";
                }

                if (!($stmt = $mysqli->prepare($tableList))) {
                    echo "Error: Prepare failed: (" . $mysqli->errno . ") " 
                        . $mysqli->error;
                }

                if (!$stmt->execute()) {
                    echo "Error: Execute failed: (" . $mysqli->errno . ") " 
                        . $mysqli->error;
                }

                $tabId = NULL;
                $tabName = NULL;
                $tabCat = NULL;
                $tabLength = NULL;
                $tabRent = NULL;

                if (!$stmt->bind_result($tabId, $tabName, $tabCat, $tabLength,
                    $tabRent)) {
                    echo "Error: Binding failed: (" . $stmt->errno . ") " 
                       . $stmt->error;
                }
                ?>

        <form action="index.php" method="POST" name="videoTableForm">
            <br/>
            <table border="1">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Length</th>
                        <th>Rented</th>
                        <th>Events</th>
                    </tr>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off');

// Populate the table rows with movie data.
while ($stmt->fetch()) {
    $tabRentTxt = ($tabRent === 0 ? 'available' : 'checked out');
    printf("<tr>\n" . "\t<td>%s</td>\n" . "\t<td>%s</td>\n" . "\t<td>%d</td>\n" 
        . "\t<td>%s</td>\n" . "\t<td><button type=\"submit\" name=\"chkInOut\""
        . " value=\"{$tabId}\">Check-in/Check-out</button>\n" . 
        "<button type=\"submit\" name=\"delete\"" 
        . " value=\"{$tabId}\">Delete</button></td>\n" 
        . "</tr>\n", $tabName, $tabCat, $tabLength, $tabRentTxt);
}
#Close fetch of $stmt
$stmt->close();

?>

                </tbody>
            </table>
        </form>
    </body>
</html>

