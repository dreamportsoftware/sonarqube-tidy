<?php

namespace SonarqubeTidy;

/**
 * Class Sonarqube
 *
 * Handles interaction with Sonarqube server
 *
 * @package SonarqubeTidy
 */
class Sonarqube extends Api
{
    public function getProjects()
    {
        $proj = $this->client->request(
            'GET',
            $this->config['sonarqube_url'].'/api/projects/index',
            [
                'auth' => [$this->username, $this->password],
            ]
        );

        return json_decode($proj->getBody(), true);
    }

    /**
     * Delete a single project (don't use Bulk Update because many projects = proxy timeout.
     * @param $projectToDelete the project key to delete
     * @return int an HTTP status code of the result.
     */
    public function deleteProject($projectToDelete)
    {
        $deletions = $this->client->post(
            $this->config['sonarqube_url'].'/api/projects/bulk_delete',
            [
                'auth' => [$this->username, $this->password],
                'multipart' => [
                    [
                        'name' => 'keys',
                        'contents' => $projectToDelete,
                    ],
                ],
            ]
        );

        return $deletions->getStatusCode();
    }
}
