<?php

namespace AppBundle\Service;

/**
 * Interface for file transfers.
 */

interface RepoEdanInterface {

  /**
   * Query EDAN
   *
   * $param array Query parameters
   * @return  array  The query result
   */
  public function queryEdan($params = array());

  /**
   * Get Record
   *
   * @param   object  $url  The EDAN URL
   * @return  array  The query result
   */
  public function getRecord($url = null);

  /**
   * Freetext Processor
   *
   * @param   array  $record          Record data from EDAN.
   * @param   array  $desired_labels  An array of labels to apply.
   * @return  array  The processed freetext data.
   */
  public function freetextProcessor($record, $desired_labels = array());

  /**
   * Freetext Logic
   *
   * @param   string  $facet           The facet.
   * @param   array   $values          The facet values.
   * @param   array   $desired_labels  An array of labels to apply.
   * @return  array   The processed freetext data.
   */
  public function freetextLogic($facet, $values, $desired_labels = array());

  /**
   * Linker
   *
   * Create links out of various values.
   *
   * @param  string  $content  The content value
   * @param  string  $facet    The facet
   * @param  string  $type     The EDAN record type
   * @param  string  $label    The desired label
   * @return string  The content value, wrapped in an HTML anchor tag
   */
  public function linker($content, $facet, $type = false, $label = false);

  /**
   * Process camelcase and underscores
   *
   * @param   string  $label  The label
   * @return  string  The cleaned-up label, in title case.
   */
  public function processCamelcaseUnderscores( $label = false );

  /**
   * Get EDANMDM Images
   *
   * Get an edanmdm record's images.
   *
   * @param   array  $record  The EDAN record
   * @return  array  The array of image data
   */
  public function edanmdmImagesProcessor( $record = false );

  /**
   * Add EDAN Data to JSON
   *
   * @param string $json_path Path to json file.
   * @param int $item_id The item iD.
   * @return json
   */
  public function addEdanDataToJson($json_path = null, $item_id = null);

}
