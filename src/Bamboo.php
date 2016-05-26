<?php

namespace SonarqubeTidy;

/**
 * Class Bamboo
 *
 * Handles interaction with Bamboo server
 *
 * @package SonarqubeTidy
 */
class Bamboo extends Api
{
    /**
     * @param $branchName The branch name to check in Bamboo
     *
     * @return bool true if it needs deleting.
     */
    public function checkBranch($branchName)
    {
        // Filter out the part of the branch name we need.
        $branchName = explode(':', $branchName);

        if (empty($branchName[2])) {
            return false;
        }

        // Ignore if in ignore-list
        if (!empty($this->config['ignore-branches']['branch']) &&
            is_array($this->config['ignore-branches']['branch'])
        ) {
            if (in_array($branchName[2], $this->config['ignore-branches']['branch'])) {
                return false;
            }
        }

        // Convert characters from bitbucket flow
        $branchName = str_replace('/', '-', $branchName[2]);

        // Check if it exists
        $build = $this->client->request(
            'GET',
            $this->config['bamboo_url'].'/rest/api/latest/quicksearch?os_authType=basic&searchTerm='.$branchName,
            ['auth' => [$this->username, $this->password]]
        );
        $bambooData = json_decode($build->getBody(), true);

        if (empty($bambooData['searchResults'])) {
            return true;
        }

        // Project found, so we don't want to delete it
        return false;
    }
}
