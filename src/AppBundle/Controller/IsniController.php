<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\DependencyInjection\Container;
use PDO;

use AppBundle\Utils\AppUtilities;

class IsniController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController();
    }

     /**
     * @Route("/admin/isni/{isniQuery}/{startRecord}/{maximumRecords}", name="query_isni", methods="GET", defaults={"isniQuery" = false, "startRecord" = 1, "maximumRecords" = 20})
     *
     * Query ISNI
     *
     * Run a query against ISNI's API
     * Example: http://isni.oclc.nl/sru/?query=pica.nw+%3D+%22NASM%22&operation=searchRetrieve&recordSchema=isni-b&startRecord=1&maximumRecords=20
     *
     * @param   object  $request     Request object
     * @return  array|bool          The query result
     */
    public function queryIsni(Request $request)
    {
        $data = array();
        $query = $request->attributes->get('isniQuery');
        $startRecord = $request->attributes->get('startRecord');
        $maximumRecords = $request->attributes->get('maximumRecords');

        if($query) {

            $url = 'http://isni.oclc.nl/sru/';
            $params = array(
                'query' => 'pica.nw = "' . $query . '"',
                'operation' => 'searchRetrieve',
                'recordSchema' => 'isni-b',
                'startRecord' => $startRecord,
                'maximumRecords' => $maximumRecords,
            );

            $results = $this->callApi($url, $params);
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
                    $data[$i]["organisationType"] = isset($isniRecordArray["organisationType"]) ? $isniRecordArray["organisationType"] : array();

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

    /**
     * Get ISNI Record
     *
     * Run a query to retrieve one ISNI record from the database.
     *
     * @param   int $isni_id  The ISNI ID
     * @return  array|bool       The query result
     */
    public function get_isni_data_from_database($isni_id, $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM isni_data
            WHERE isni_data.active = 1
            AND isni_id = :isni_id");
        $statement->bindValue(":isni_id", $isni_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insert/Update ISNI Data
     *
     * Run queries to insert and update ISNI data in the database.
     *
     * @param   array   $data        The data array
     * @param   int     $isni_id     The project ID
     * @param   string  $isni_label  The ISNI label
     * @param   object  $conn        Database connection object
     * @return  int     The project ID
     */
    public function insert_isni_data($isni_id = FALSE, $isni_label = FALSE, $user_id, $conn)
    {
        if($isni_id && $isni_label) {
            $statement = $conn->prepare("INSERT INTO isni_data
                    (isni_id, isni_label, date_created, created_by_user_account_id, last_modified_user_account_id)
                    VALUES (:isni_id, :isni_label, NOW(), :user_account_id, :user_account_id)");
            $statement->bindValue(":isni_id", $isni_id, PDO::PARAM_INT);
            $statement->bindValue(":isni_label", $isni_label, PDO::PARAM_STR);
            $statement->bindValue(":user_account_id", $user_id, PDO::PARAM_INT);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `isni_data` failed.');
            }

            return $last_inserted_id;
        }
    }


}
