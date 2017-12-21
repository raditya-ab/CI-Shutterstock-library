# CI-Shutterstock-library
Libraries for Shutterstock API, written in PHP for Codeigniter Framework


This is my custom libraries, tested on **Codeigniter v3.1.6**

###**Important note:**
- To retrieve Client ID & Client Secret, you have to register in https://developers.shutterstock.com/
- To download images you must have an active subscription id, and then authenticate using OAuth2.0, it's described in the API Docs.

###**Difference**
- If you authenticate using OAuth, your access token will remain and not need to be changed, but if you using regular authentication the key will be have to be updated
every several hours (I don't know exactly).

###**References :**
- **CodeIgniter**: [CodeIgniter](https://codeigniter.com)
- **Shutterstock API**: [Shutterstock API](https://developers.shutterstock.com/)
- **cURL**: [cURL : How to use](https://curl.haxx.se/docs/manpage.html)

Thank you, if you think you can add some improvement or fixes, please generate a pull request.