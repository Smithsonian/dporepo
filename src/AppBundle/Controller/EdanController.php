<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Service\RepoEdan;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class EdanController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
     * @var object $edan
     */
    private $edan;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, RepoEdan $edan)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->edan = $edan;
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
            $results = $this->edan->queryEdan($parameters);

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
     * @Route("/admin/edan_record/{url}", name="get_edan_record", methods="GET", defaults={"url" = false})
     *
     * Get EDAN Record
     *
     * @param   object  $request  Request object
     * @return  array  The query result
     */
    public function getEdanRecord(Request $request)
    {
      $data = array();

      if($request->attributes->get('url')) {

        $data = $this->edan->getRecord( $request->attributes->get('url') );

        // If the EDAN record doesn't exist, throw a createNotFoundException (404).
        if(isset($data['error'])) throw $this->createNotFoundException('The EDAN record does not exist');
      }
      
      // Return JSON.
      return $this->json($data);
    }

}
