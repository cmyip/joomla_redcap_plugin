# Plugin for Joomla to search REDCap projects

## Installation

1. Install redcap plugin in redcap/api folder
   - Copy `redcap/project_list_api.php` to your redcap installation's `api` folder
2. Generate superuser API token in redcap
   - In redcap, open `Control Center > API Tokens`
   - Under Super API Tokens, select a user in dropdown list and click on Create if you have not
   - View the API Token, this token will be used in Joomla configuration later
3. Compress contents of joomla folder to a ZIP
4. Install the package in joomla
   - In Joomla Go to `Extensions > Manage > Install`
5. Setup plugin in joomla
    - In Joomla administration interface, open `Extensions > Plugins`
    - Search for "Redcap"
    - The following fields are required
    - `API Endpoint` is the URL to the file installed in step 1
    - `API Token` is generated in step 2
    - `Username` is the username used in step 2
    - `Base URL` is the URL to redirect user to when joomla user clicks on search results

## Contributing
Feel free to fork this to your own use, do create issue if there are missing parts