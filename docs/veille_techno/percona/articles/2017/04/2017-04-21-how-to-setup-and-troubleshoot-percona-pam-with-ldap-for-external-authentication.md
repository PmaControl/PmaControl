---
title: How to Setup and Troubleshoot Percona PAM with LDAP for External Authentication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-setup-and-troubleshoot-percona-pam-with-ldap-for-external-authentication/
  post_id: 16699
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2017-04-21T16:20:39'
published_at_gmt: '2017-04-21T16:20:39'
modified_at: '2026-05-05T18:36:06'
modified_at_gmt: '2026-05-05T18:36:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
- Security
category_slugs:
- insight-for-dbas
- mysql
- security
tags:
- Active Directory
- LDAP
- PAM
- Samba
tag_slugs:
- active-directory
- ldap
- pam
- samba
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PerconaServer-Vertical-RGB-01-e1494438055631.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Setup and Troubleshoot Percona PAM with LDAP for External Authentication

Source: [Percona Blog](https://www.percona.com/blog/how-to-setup-and-troubleshoot-percona-pam-with-ldap-for-external-authentication/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2017-04-21T16:20:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll look at how to setup and troubleshoot the Percona PAM authentication plugin. We occasionally get requests from our support clients on how to get Percona Server for MySQL to authenticate with an external authentication service via LDAP or Active Directory. However, we normally do not have access to client’s infrastructure to … Continued

## Structure detectee

- H2: Compile and Install Samba
- H2: Create a domain environment with Samba
- H2: Add users and groups to this domain
- H2: How to get Percona Server to use these accounts for authentication via LDAP
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How to Setup and Troubleshoot Percona PAM with LDAP for External Authentication](https://www.percona.com/wp-content/uploads/2026/03/PerconaServer-Vertical-RGB-01-e1494438055631.png)
- content / image: [Percona PAM](https://www.percona.com/wp-content/uploads/2026/03/PerconaServer-Vertical-RGB-01-e1477065302931.png)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
