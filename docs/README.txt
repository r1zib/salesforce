README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "/home/erwand/workspace/azeliz/nke/public"
   ServerName nke.local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development

   <Directory "/home/erwand/workspace/azeliz/nke/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>


La base de donnée 
=================
Table User :
Les mot de passe sont mis en clair dans la base de donnée car on besoin du mot de passe pour accéder à salesforce.



