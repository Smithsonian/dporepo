<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Utils\AppUtilities;

class IsniController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
    }

     /**
     * @Route("/admin/isni/{isni_query}", name="query_isni", methods="GET", defaults={"isni_query" = false})
     *
     * Query ISNI
     *
     * Run a query against ISNI's API
     * Example: http://isni.oclc.nl/sru/?query=pica.nw+%3D+%22NASM%22&operation=searchRetrieve&recordSchema=isni-b
     *
     * @param   object  $request     Request object
     * @return  array|bool          The query result
     */
    public function queryIsni(Request $request)
    {
        $data = array();
        $query = $request->attributes->get('isni_query');

        if($query) {

            $url = 'http://isni.oclc.nl/sru/';
            $params = array(
                'query' => 'pica.nw = "' . $query . '"',
                'operation' => 'searchRetrieve',
                'recordSchema' => 'isni-b',
            );

            // $this->u->dumper(http_build_query($params));
            $results = $this->callApi($url, $params);
            // $this->u->dumper($results);
            $xml = simplexml_load_string($results);
            $xml->registerXPathNamespace('srw', 'http://www.loc.gov/zing/srw/');

            $records = $xml->xpath('//srw:records');

            foreach ($records as $rec) {

                $recordData = $rec->xpath('//srw:recordData');

                $i = 0;
                foreach ($recordData as $r) {

                    $isniId = json_decode(json_encode($r->responseRecord->ISNIAssigned->isniUnformatted), true);
                    $data[$i]['isniId'] = $isniId[0];

                    $isniRecord = $r->responseRecord->ISNIAssigned->ISNIMetadata->identity->organisation;
                    $isniRecordArray = json_decode(json_encode($isniRecord), true);
                    $data[$i]["organisationType"] = $isniRecordArray["organisationType"];

                    if(isset($isniRecordArray["organisationName"])) {
                        foreach ($isniRecordArray["organisationName"] as $key => $value) {
                            if(is_array($value)) {
                                // if(isset($data[$i]["organisationName"]) && !in_array($value["mainName"], $data[$i]["organisationName"])) {
                                    $data[$i]["organisationName"][] = $value["mainName"];
                                // }
                            } else {
                                // if(!in_array($value, $data[$i]["organisationName"])) {
                                    $data[$i]["organisationName"][] = $value;
                                // }
                            }
                        }
                    }

                    if(isset($isniRecordArray["organisationNameVariant"])) {
                        foreach ($isniRecordArray["organisationNameVariant"] as $key => $value) {
                            if(is_array($value)) {
                                // if(!in_array($value["mainName"], $data[$i]["organisationNameVariant"])) {
                                    $data[$i]["organisationNameVariant"][] = $value["mainName"];
                                // }
                            } else {
                                // if(!in_array($value, $data[$i]["organisationNameVariant"])) {
                                    $data[$i]["organisationNameVariant"][] = $value;
                                // }
                            }
                        }
                    }

                    $i++;
                }
                
            }
            // $this->u->dumper($data);

        }

        return $this->json($data);
    }

    function callApi($url = false, $params = false)
    {
        if ($url && $params) {
            $curl = curl_init();
            $url = sprintf("%s?%s", $url, http_build_query($params));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
        }

        return $result;
    }

}
