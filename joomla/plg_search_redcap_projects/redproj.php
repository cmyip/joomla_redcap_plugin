<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Search.redcap
 *
 * @copyright   Open source
 * @license     GNU General Public License
 */

defined('_JEXEC') or die;

/**
 * Redcap search plugin.
 *
 */
class PlgSearchRedproj extends JPlugin
{
    private $apiKey = null;
    private $targetUrl = null;
    private $apiUsername = null;
    private $projectBaseurl = null;

	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'redproj' => 'Redcap Projects'
		);

		return $areas;
	}

    public function callApi($text) {
        $params = [
            'username' => $this->apiUsername,
            'token' => $this->apiKey,
            'name' => $text
        ];
        $queryParams = http_build_query($params, '', '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->targetUrl . "?" . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $output = curl_exec($ch);
        return json_decode($output);
    }

    private function mapToObject($redcap_project) {
        $newClass = new stdClass();
        $newClass->title = $redcap_project->name;
        $newClass->text = $redcap_project->description;
        $newClass->section = 'Redcap Project';
        $newClass->content = $redcap_project->description;
        $newClass->created = $redcap_project->created_at;
        $newClass->href = sprintf($this->projectBaseurl, $redcap_project->id);
        return $newClass;
    }

	/**
	 * Calls Redcap API to get a list of porjects
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db         = JFactory::getDbo();
		$serverType = $db->getServerType();
		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$groups     = implode(',', $user->getAuthorisedViewLevels());
		$tag        = JFactory::getLanguage()->getTag();
		
		if (is_array($areas) && !array_intersect($areas, array_keys($this->onContentSearchAreas())))
		{
			return array();
		}

        $this->targetUrl = $this->params->get('target_url');
        $this->apiUsername = $this->params->get('api_username');
        $this->apiKey = $this->params->get('api_key');
        $this->projectBaseurl = $this->params->get('project_baseurl');


		$text = trim($text);

		if ($text === '')
		{
			return array();
		}

        $response = $this->callApi($text);
        if (!$response->success) {
            return array();
        }

        $results = [];

        foreach ($response->data as $redcap_project) {
            $mappedObject = $this->mapToObject($redcap_project);
            $results[] = $mappedObject;
        }

		return $results;
	}

}
