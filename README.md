Sonarqube Tidy
==============

Sonarqube Tidy is a simple tool that checks for active branches in Bamboo vs ones in Sonarqube. If the Bamboo branch doesn't exist any more
then the Sonarqube branch is deleted. This is useful when using Sonar4Bitbucket plugin where it creates a project per branch for Pull Request
purposes.

Installation
------------
To install the configuration and setup the application the first time, run:

    composer create-project

Running
-------
    bin/sqt run
    
To run in 'non-interactive' mode, you can specify `--username=<username>` and/or `--password=<password>` on the command-line although
this is not recommended.
    
Configuration
-------
The configuration file is created at `config/config.ini`. It needs to contain the URL to your Bamboo and Sonarqube servers along with
any branches you should ignore from this process (such as master and develop if you're using gitflow).

We currently assume your Sonarqube and Bamboo instances share a user directory for authentication.


