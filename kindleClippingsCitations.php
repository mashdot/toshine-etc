<?php // Time-stamp: <2011-07-07 Thu 20:01 kindleClippingsCitations.php> 'Mash (Thomas Herbert) | http://toshine.org

// A small PHP function to convert an uploaded Amazon Kindle "My Clippings File"
// and convert it common academic citation styles.
// This file is part of http://toshine.org/etc/citing-amazon-kindle-ebook-clippings-highlights-chicago-mla-apa-citation-styles/
// Kudos: http://zacvineyard.com/blog/2010/03/15/a-better-php-upload-and-rename-script/

// citation style (see switch for options.)
$style = 'chicago';

// ----------[ CHECK FILE ]----------
// check correct file extension and rename to prevent duplicates.
$fileName = $_FILES['file']['name'];
$fileBaseName = substr($fileName, 0, strripos($fileName, '.')); // get file extention
$fileExt = substr($fileName, strripos($fileName, '.')); // get file name
$fileSize = $_FILES['file']['size'];

if (($fileExt == '.txt')  &&  ($fileSize < 50000)) {

  $newFileName = 'kindle-citations-'.$style.'-' . date('dmYHis') . $fileBasename . $fileExt; // rename file

  // if file already exists
  if (file_exists('upload/' . $newFileName)) {
    print 'ERROR: File is already uploaded.';

  } else {

    // move and create file path
    move_uploaded_file($_FILES['file']['tmp_name'], 'upload/' . $newFileName);
    $filePath = 'upload/'.$newFileName;
  }

  // file selection error
} elseif (empty($fileBaseName)) {
  print '<p>ERROR: Please upload a .txt file.';

  // file size error
} elseif ($fileSize > 10000) {
  echo '<p>ERROR: File is too large.</p>';

} else {

  // file type error
  echo '<p>ERROR: Only .txt files are allowed.</p>';
  unlink($_FILES['file']['tmp_name']);
}

// ----------[ KINDLE CLIPPINGS CONVERSION ]----------
// this exists as a standalone function. see: http://github.com/mashdot
if (is_file($filePath)) {
  $fileData = file_get_contents($filePath);

  // trim off last '=========='.
  $fileData = substr($fileData, 0, strrpos($fileData, '=========='));

  // cleanup nasty entities.
  $dirty = array('‘','’','“','”');
  $clean = array("'","'",'"','"');
  //$clean = array('&#8216;','&#8217;','&#8220;','&#8221;');
  $fileData = str_replace($dirty, $clean, $fileData);

  // split into clips.
  $fileArray = explode('==========',$fileData);
  //$fileArray = array_reverse($fileArray);

  // how many?
  if ($limit > '0') { $fileArray = array_slice($fileArray, 0, $limit); }

  // open file for writing
  $fileHandle = fopen($filePath, 'w') or die('Unable to open file.');

  // fetch bits of clippings.
  foreach ($fileArray as $clipping) {

    // 4 hours of regex hacking!
    // email me if you can optimise this better.
    $regex = '/(.+)\((.+)\)\s+(?:.+?Page\s+(\d+))*.+?Loc\.\s+(\d+-*\d+).+?on\s+(.+)\s+(.+)/m';
    // 11.04.2011 $regex = '/(.+)\((.+)\)\s(?:.+?Page\s(\d+))*.+?Loc\.\s(\d+-*\d+).+?on\s(.+)\s+(.+)/';

    preg_match($regex,$clipping,$matches);

    // create variables.
    $title = trim($matches[1]);
    $author = trim($matches[2]);
    $page = trim($matches[3]);
    $location = trim($matches[4]);
    $date = trim($matches[5]);
    $quote = trim($matches[6]);

    // output depending on radio button citation style selection.
    switch($style){
    case 'toshine':
      // Quote -- Author. Title. Page. Location. Date.
      $citations = "#+begin_quote\n$quote -- $author. $title. pg. $page, loc. $location. $date.\n#+end_quote\n\n";
      break;
    case 'chicago':
      // Quote. Author, Title, Page, Location. Kindle Edition.
      $citations = "$quote $author, $title, pg. $page, loc. $location. Kindle Edition.\n\n";
      break;
    case 'apa':
      // Quote. Author, Title [Kindle Edition]. Page. Location.
      $citations = "$quote $author, $title. [Kindle Edition]. pg. $page, loc. $location.\n\n";
      break;
    case 'mla':
      // Quote. Author, Title. Kindle Edition. Page. Location. Accessed: Date.
      $citations = "$quote $author, $title. Kindle Edition. pg. $page, loc. $location. Accessed: $date.\n\n";
      break;
    }
    // write to file
    fwrite($fileHandle, $citations);
  }
  // close file
  fclose($fileHandle);
  print '<h2>Download File.</h2><p>Right click and save as: <a rel="nofollow" href="'.$filePath.'">'.$newFileName.'</a></p><hr>';

  // print citations on screen
  $citationsFile = file_get_contents($filePath);
  print '<h2>File Output ('.$style.').</h2><pre>'.$citationsFile.'</pre>';
}
?>
