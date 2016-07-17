<?php
/**
 * @file Provides the class that takes a trello json file and outputs a csv file.
 */

class TrelloBoardToCsv {
  public $url = '';
  public $json = '';
  public $csv = '';
  public $list = '';
  private $curl;

  /**
   * Constructor
   * Checks that the input file exists and kicks off the sequence.
   */
  function __construct($url, $list = '') {
    $this->curl = curl_init();     
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false); 
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);   

    $this->url = $url;
    $this->list = $list;
  }
  

  function getMarkdown()
  {
    if (isset($this->url)) {  
      $this->file_to_json();
      $this->json_to_csv();
      return $this->get_markdown();
    }
    else {
      throw new Exception('File does not exist.');
    }
  }

  /**
   * Read in the file and decodes the json.
   */
  function file_to_json() {
    $file = $this->http_get($this->url);
      // print_r (curl_error($this->curl));

    // echo "wew $file";
    if ($file === FALSE) {
      throw new Exception('Could not read file.');
    }
    $decoded = json_decode($file);
    if ($decoded === NULL) {
      throw new Exception('Could not decode the JSON.');
    }
    $this->json = $decoded;
  }

  /**
   * Takes the decoded json and saves the interesting data.
   */
  function json_to_csv() {
    $output = '';

    //card name output
    $output .= '# ' . $this->json->name . '' . "\n";
    
    foreach ($this->json->lists as $i => $list) {
      $print_list = FALSE;
      
      /**
       * Test if we should display this list.
       * Only print open lists,
       * And if the list title argument is not empty, only print lists that contain
       * that string.
       */
      if ($list->closed == FALSE) {

        $print_list = TRUE;
        
        if ($this->list != '') {
          if (strpos($list->name, $this->list) === FALSE) {
            $print_list = FALSE;
          }
        }

      }

      if ($print_list) {

        $output .= "\n" . '**' . $list->name . '**' . "\n";

        foreach ($this->json->cards as $j => $card) {
          if ($card->closed == FALSE && $card->idList == $list->id) {
            $output .= '* ' . $card->name . '' . "\n";
          }
        } // end card foreach


      }
    } // end list foreach

    $this->csv = $output;
  }
  
  /**
   * Creates the CSV file
   */
  function save_csv() {
    $result = file_put_contents($this->url . '.csv', $this->csv);
    if ($result === FALSE) {
      throw new Exception('Could not write the file.');
    }
  }

  function get_markdown()
  {
    return $this->csv;
  }

  private function http_get($url) {
    curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
    return $this->_request($url);
  }

  private function _request($url) {
    curl_setopt($this->curl, CURLOPT_URL, $url);
    // curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($this->curl, CURLOPT_USERAGENT, 'My GitHub App');
    // curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Accept: application/vnd.github-blob.raw'));
    return curl_exec($this->curl);
  }

}
