<?php // Time-stamp: <2011-06-24 Fri 20:18 kindleClippings.php> 'Mash (Thomas Herbert) | http://toshine.org

// A small PHP function with some regex magic to pull data from the Amazon Kindle "My Clippings.txt" file.

// kindle clippings.
function kindleClippings($file,$limit) {
  if (is_file($file)) {
    $fileData = file_get_contents($file);

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

      // print out clippings.
      // you can output as you want, currently I output in Emacs Org-Mode
      // quote format to be used with my site http://toshine.org/tolle-lege/ .
      print '<pre>';
      print '#+begin_quote<br>';
      print $quote.' -- '.$author.'. '.$title.'. pg. '.$page.', loc. '.$location.'. '.$date.'.<br>';
      print '#+end_quote<br><br>';
      print '</pre>';
    }
  }
}

// Run function
kindleClippings('myclippings.txt','0');

?>
