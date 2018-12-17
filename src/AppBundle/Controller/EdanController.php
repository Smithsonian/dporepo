<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

use Smithsonian\EdanClient\EdanClient;
use Smithsonian\EdanClient\Util\Settings;
// use Smithsonian\EdanClient\Util\Facets;

class EdanController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    /**
     * @var string 
     */
    private $edan_url;
    /**
     * @var string 
     */
    private $edan_app_id;
    /**
     * @var string 
     */
    private $edan_auth_token;
    /**
     * @var string 
     */
    private $edan_version;
    /**
     * @var string 
     */
    private $edan_search_endpoint;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, string $edan_url, string $edan_app_id, string $edan_auth_token, string $edan_version, string $edan_search_endpoint)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;

        // EDAN Client Settings
        $this->edanSettings = new Settings(
            $edan_url,
            $edan_app_id,
            $edan_auth_token,
            $edan_version,
            Settings::REQUEST_SIGNED,
            TRUE
        );

        // EDAN Client
        $this->edanClient = new EdanClient($this->edanSettings);

        // EDAN Endpoint
        $this->edan_search_endpoint = $edan_search_endpoint;

        $this->metadata_fields = array(
            'name',
            'date',
            'place',
            'topic',
            'culture',
            'objectType',
            'physicalDescription',
            'name',
            'date',
            'galleryLabel',
            'notes',
            'taxonomicName',
            'language',
            'publisher',
            'place',
            'setName',
            'creditLine',
            'objectRights',
            'identifier',
            'topic',
            'culture',
            'dataSource'
        );
    }

    /**
     * @Route("/admin/edan/{q}/{format}/{page}/{start}/{rows}", name="query_edan", methods="GET", defaults={"q" = false, "format" = "json", "page" = 1, "start" = 0, "rows" = 10})
     *
     * Query EDAN
     *
     * @param   object  $request  Request object
     * @return  array  The query result
     */
    public function queryEdan(Request $request)
    {
        $data = array();

        if($request->attributes->get('q')) {

            $data['previous_page'] = ((int)$request->attributes->get('page') > 1) ? ((int)$request->attributes->get('page') - 1) : '';
            $data['next_page'] = ((int)$request->attributes->get('page') + 1);
            $data['start'] = !empty($data['previous_page']) ? ((int)$request->attributes->get('rows') * (int)$request->attributes->get('page') - (int)$request->attributes->get('rows')) : 0;

            // Setup the parameters.
            $parameters = array(
                'q' => $request->attributes->get('q'),
                'start' => $data['start'],
                'rows' => (int)$request->attributes->get('rows'),
            );

            // Query EDAN
            $results = $this->edanClient->fetchEdanResponse($parameters, $this->edan_search_endpoint);

            // Get the protected property 'data' from the $results object.
            $data = array_merge($data, $results->getData());

            if($data['numFound'] > 0) {

                // If we're at the end of the results, set the next page to an empty value.
                $data['next_page'] = (($data['start'] + (int)$request->attributes->get('rows')) <= $data['numFound']) ? $data['next_page'] : '';

                // Process EDAN's freetext and images.
                foreach ($data['rows'] as $key => $value) {
                    // Process freetext
                    $data['rows'][$key]['processed_freetext'] = $this->freetextProcessor($value, $this->metadata_fields);
                    // Process images
                    $images = $this->edanmdm_images_processor($value);
                    if(!empty($images) && !empty($images['record_images'])) {
                        $data['rows'][$key]['primary_image'] = $images['record_images'][0];
                    }
                }
            }
        }

        // Return HTML
        if($request->attributes->get('format') === 'html') {
            return $this->render('edanResults/results.html.twig', array(
                'page_title' => 'Results',
                'data' => $data,
            ));
        }
        
        // By default, return JSON.
        return $this->json($data);
    }

    /**
     * Freetext Processor
     *
     * @param   array  $record          Record data from EDAN.
     * @param   array  $desired_labels  An array of labels to apply.
     * @return  array  The processed freetext data.
     */
    private function freetextProcessor($record, $desired_labels = array()) {

      $new_freetext = array();

      if (isset($record['content']['freetext'])) {
        foreach ($record['content']['freetext'] as $facet => $values) {
          // Don't process facets found in the blacklist.
          // If desired labels were passed, process...
          // insuring that the order of the desired labels is kept.
          if( !empty($desired_labels) && in_array($facet, $desired_labels) ) {
            // Get the label key.
            $desired_label_key = array_search($facet, $desired_labels);
            // Apply the label key to the $new_freetext array.
            $new_freetext[$desired_label_key] = $this->freetext_logic($facet, $values, $desired_labels);
          }
          // If no desired labels were passed, don't set the key.
          if( empty($desired_labels)) {
            $new_freetext[] = $this->freetext_logic($facet, $values);
          }
        }
        // Sort the array by key.
        ksort($new_freetext);
        // Update freetext
        if(empty($desired_labels)) {
          $record['content']['freetext'] = $new_freetext;
        }
      }

      return $new_freetext;
    }

    /**
     * Freetext Logic
     * Works hand-in-hand with the _freetextProcessor() method.
     *
     * @param   string  $facet           The facet.
     * @param   array   $values          The facet values.
     * @param   array   $desired_labels  An array of labels to apply.
     * @return  array   The processed freetext data.
     */
    private function freetext_logic($facet, $values, $desired_labels = array()) {

      $label_name = !empty($desired_labels) ? 'label': 'facet';

      $new_obj = array(
        $label_name => $this->process_camelcase_underscores($facet),
        'values' => array()
      );

      $new_values = array();

      // Create links out of certain values.
      if(!empty($values)) {
        foreach ($values as $_value) {
          // $new_values[$_value['label']][] = $this->linker($_value['content'], $facet, false, $_value['label']);
          $new_values[$_value['label']][] = $_value['content'];
        }
      }

      foreach ($new_values as $label => $_values) {
        // Process camelcase in the label.
        $label = $this->process_camelcase_underscores($label);
        $new_obj['values'][] = array(
          'label' => ucwords($label),
          'values' => $_values
        );
      }

      return $new_obj;
    }

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
    private function linker($content, $facet, $type = false, $label = false) {
      switch ($facet) {
        case 'name':
        case 'setName':
          if(!empty($label)) {
            if($label == 'See more items in') {
              // return '<a href="/search/collections?edan_q=' . $content . '">' . $content . '</a>';
              return $content;
            } elseif($label == 'On View') {
              // return '<a href="/search/collections?edan_local=1&edan_q[]=' . $content . '">' . $content . '</a>';
            } else {
              return $content;
            }
          }
          break;
        case 'topic':
          // return '<a href="/search/collections?edan_local=1&edan_q[]=' . $content. '">' . $content . '</a>';
        default:
          return $content;
      }
    }

    /**
     * Process camelcase and underscores
     *
     * @param   string  $label  The label
     * @return  string  The cleaned-up label, in title case.
     */
    private function process_camelcase_underscores( $label = false ) {
      
      if($label) {
        // Force the first character to be lowercase (for strings like ObjectType).
        $label = preg_replace('/(?<!^)([A-Z])/', ' \\1', $label);
        // Process camelcase in the label.
        $new_label = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', $label);
        // Process underscores in the label, and convert to title case.
        $new_label = ucwords(str_replace('_', ' ', $label));
        // Return the new label.
        return $new_label;
      }

    }

    /**
     * Get EDANMDM Images
     *
     * Get an edanmdm record's images.
     *
     * @param   array  $record  The EDAN record
     * @return  array  The array of image data
     */
    private function edanmdm_images_processor( $record = false ) {

      $data = array();
      $data['record_images'] = array();
      $data['all_image_ids'] = array();
      $ids_url = 'https://ids.si.edu/ids/deliveryService/';
      $thumbnail_size = '&max_h=120';
      $medium_size = '&max_h=150&max_w=150';
      $regular_size = '&max_h=500&max_w=500';
      $gallery_size = '&max_h=900&max_w=900';

      if( !empty($record['content']['descriptiveNonRepeating']['online_media']['media']) &&
          ((int)$record['content']['descriptiveNonRepeating']['online_media']['mediaCount'] >= 1) ) {

        foreach($record['content']['descriptiveNonRepeating']['online_media']['media'] as $key => $media) {

          $media['record_id'] = $record['id'];
          $media['record_title'] = htmlspecialchars($record['content']['descriptiveNonRepeating']['title']['content']);
          $media['record_date'] = !empty($record['content']['freetext']['date']) ? $record['content']['freetext']['date']['0']['content'] : '';
          $media['record_material'] = !empty($record['content']['freetext']['physicalDescription']) ? $record['content']['freetext']['physicalDescription']['0']['content'] : '';
          $media['record_contributor'] = !empty($record['content']['freetext']['name']) ? $record['content']['freetext']['name']['0']['content'] : '';

          // If there is no idsId, go for the thumbnail.
          if(empty($media['idsId']) && ($media['type'] !== 'Youtube videos')) {
            $parsed = parse_url($media['thumbnail']);
            if(!empty($parsed['host']) && ($parsed['host'] === 'ids.si.edu')) {
              parse_str($parsed['query'], $params);
              $mediaThumbnail = $params['id'];
            } else {
              $mediaThumbnail = $media['thumbnail'];
            }
            $media['idsId'] = $mediaThumbnail;
            $media['thumbnail'] = $ids_url . '?id=' . $mediaThumbnail . $thumbnail_size;
            $media['medium_size'] = $ids_url . '?id=' . $mediaThumbnail . $medium_size;
            $media['regular_size'] = $ids_url . '?id=' . $mediaThumbnail . $regular_size;
            $media['gallery_size'] = $ids_url . '?id=' . $mediaThumbnail . $gallery_size;
            $media['full_size'] = $ids_url . '?id=' . $mediaThumbnail;
          }

          // If there is an idsId, and it's not a PDF, use the idsId.
          // If there is no idsId, go for the thumbnail.
          if(array_key_exists('idsId', $media) && (strtolower(pathinfo($media['idsId'], PATHINFO_EXTENSION)) !== 'pdf')) {
            // Check for '/192X192' in the idsId, and remove it.
            // TODO: There may be others out there... so may need to revisit.
            $media['idsId'] = str_replace('/192X192', '', $media['idsId']);
            $media['thumbnail'] = $ids_url . '?id=' . $media['idsId'] . $thumbnail_size;
            $media['medium_size'] = $ids_url . '?id=' . $media['idsId'] . $medium_size;
            $media['regular_size'] = $ids_url . '?id=' . $media['idsId'] . $regular_size;
            $media['gallery_size'] = $ids_url . '?id=' . $media['idsId'] . $gallery_size;
            $media['full_size'] = $ids_url . '?id=' . $media['idsId'];
            array_push($data['all_image_ids'], $media['idsId']);
          } else {
            // Get the id from the url.
            parse_str( parse_url( $media['thumbnail'], PHP_URL_QUERY), $url_array );
            if(!empty($url_array['id'])) {
              $media['thumbnail'] = $ids_url . '?id=' . $url_array['id'] . $thumbnail_size;
              $media['medium_size'] = $ids_url . '?id=' . $url_array['id'] . $medium_size;
              $media['regular_size'] = $ids_url . '?id=' . $url_array['id'] . $regular_size;
              $media['gallery_size'] = $ids_url . '?id=' . $url_array['id'] . $gallery_size;
              $media['full_size'] = $ids_url . '?id=' . $url_array['id'];
            } else {
              $media['regular_size'] = $media['full_size'] = $media['thumbnail'];
            }
          }

          // Stitch it all together.
          switch ($media['type']) {
            case 'Images':
            case 'URL':
              // If the thumbnail image is a yellow placeholder, funnel it through IDS.
              if(stristr($media['thumbnail'], 'dreference.jpg')) {
                $media['thumbnail'] = $ids_url . '?id=' . $media['thumbnail'] . $thumbnail_size;
              }
              $data['record_images'][] = $media;
            break;
            case 'Scanned books':
              $data['record_images'][] = $media;
            break;
          }

        }
      }

      $data['all_image_ids'] = json_encode($data['all_image_ids'], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

      return $data;
    }

}
