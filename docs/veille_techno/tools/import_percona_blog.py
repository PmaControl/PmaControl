#!/usr/bin/env python3
"""Import Percona blog metadata as local technical-watch Markdown notes.

The importer intentionally does not copy full Percona articles or download
their images. Percona's public site footer states "All Rights Reserved"; each
generated note keeps metadata, a short public excerpt, detected headings, and
source image URLs so the original content remains canonical.
"""

from __future__ import annotations

import argparse
import datetime as dt
import html
import json
import re
import sys
import time
import warnings
from collections import defaultdict
from pathlib import Path
from typing import Any
from urllib.parse import urljoin

import requests
import yaml
from bs4 import BeautifulSoup
from bs4 import MarkupResemblesLocatorWarning


WP_BASE_URL = "https://www.percona.com/wp-json/wp/v2"
PERCONA_BLOG_URL = "https://www.percona.com/blog/"
PMACONTROL_AUTHOR_URL = "https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/"
PMACONTROL_REFERENCE_POST_URL = "https://www.pmacontrol.fr/fr/site/blog_post/galera-has-gone-away/"

REQUEST_HEADERS = {
    "User-Agent": "PmaControl veille techno importer/1.0 (+https://www.pmacontrol.fr/)",
    "Accept": "application/json",
}

POST_FIELDS = ",".join(
    [
        "id",
        "date",
        "date_gmt",
        "modified",
        "modified_gmt",
        "slug",
        "link",
        "title",
        "excerpt",
        "content",
        "categories",
        "tags",
        "author",
        "featured_media",
        "yoast_head_json",
    ]
)

TERM_FIELDS = "id,name,slug,link,count,taxonomy"
AUTHOR_FIELDS = "id,name,slug,link,url,description,simple_local_avatar,yoast_head_json"

TOPIC_FILTERS = [
    {"type": "category", "id": 83, "topic": "MySQL", "slug": "mysql"},
    {"type": "category", "id": 1281, "topic": "MariaDB", "slug": "mariadb"},
    {"type": "category", "id": 2261, "topic": "ProxySQL", "slug": "proxysql"},
    {"type": "category", "id": 2104, "topic": "PMM", "slug": "monitoring"},
    {"type": "category", "id": 3845, "topic": "XtraBackup", "slug": "xtrabackup"},
    {"type": "tag", "id": 1758, "topic": "MaxScale", "slug": "maxscale"},
    {"type": "tag", "id": 2167, "topic": "PMM", "slug": "pmm"},
    {
        "type": "tag",
        "id": 2166,
        "topic": "PMM",
        "slug": "percona-monitoring-and-management",
    },
    {"type": "tag", "id": 378, "topic": "Percona Toolkit", "slug": "percona-toolkit"},
    {"type": "tag", "id": 330, "topic": "XtraBackup", "slug": "percona-xtrabackup"},
    {"type": "tag", "id": 153, "topic": "XtraBackup", "slug": "xtrabackup"},
]

SEARCH_FILTERS = [
    {"query": "MaxScale", "topic": "MaxScale"},
    {"query": "PMM", "topic": "PMM"},
    {"query": "Percona Monitoring and Management", "topic": "PMM"},
    {"query": "Percona Toolkit", "topic": "Percona Toolkit"},
    {"query": "XtraBackup", "topic": "XtraBackup"},
    {"query": "Percona XtraBackup", "topic": "XtraBackup"},
    {"query": "ProxySQL", "topic": "ProxySQL"},
]

CURATOR = {
    "name": "Sylvain ARBAUDIE",
    "slug": "sylvain-arbaudie",
    "role": "Architecte de données — ArBauDie.IT",
    "source_url": PMACONTROL_AUTHOR_URL,
    "article_count_source": 23,
    "bio": (
        "Architecte de données avec plus de 15 ans d'expérience, diplômé de "
        "l'ESIEA. Spécialisé dans l'écosystème MariaDB / MySQL, la performance, "
        "la sécurité et les architectures distribuées (Galera, MaxScale, "
        "ProxySQL). Contributeur référencé sur Planet MariaDB par la MariaDB "
        "Foundation. Sylvain intervient en France et en Europe auprès "
        "d'organisations comme Le Monde, Swisscom, Actility et MariaDB. "
        "Consultant indépendant via ArBauDie.IT, hébergé éthiquement en Suisse."
    ),
    "links": {
        "medium": "https://medium.com/@arbaudie.it",
        "website": "https://arbaudie.it",
        "twitter": "https://twitter.com/SylvainArbaudie",
        "linkedin": "https://linkedin.com/in/sylvain-arbaudie",
    },
}


class PerconaClient:
    def __init__(self) -> None:
        self.session = requests.Session()
        self.session.headers.update(REQUEST_HEADERS)

    def get_json(self, path: str, params: dict[str, Any] | None = None) -> tuple[Any, requests.Response]:
        url = path if path.startswith("http") else f"{WP_BASE_URL}{path}"
        last_error: Exception | None = None
        for attempt in range(1, 5):
            try:
                response = self.session.get(url, params=params, timeout=60)
                if response.status_code in {429, 500, 502, 503, 504}:
                    time.sleep(attempt * 2)
                    continue
                if 400 <= response.status_code < 500:
                    response.raise_for_status()
                response.raise_for_status()
                return response.json(), response
            except Exception as exc:  # noqa: BLE001 - retry boundary
                last_error = exc
                if attempt == 4:
                    break
                time.sleep(attempt * 2)
        raise RuntimeError(f"Request failed for {url}: {last_error}") from last_error

    def fetch_paginated(
        self,
        path: str,
        params: dict[str, Any],
        max_records: int = 0,
        progress_label: str = "",
    ) -> list[dict[str, Any]]:
        records: list[dict[str, Any]] = []
        page = 1
        total_pages = 1
        while page <= total_pages:
            page_params = dict(params, page=page, per_page=100)
            data, response = self.get_json(path, page_params)
            if not isinstance(data, list):
                raise RuntimeError(f"Expected a list from {path}, got {type(data).__name__}")
            if page == 1:
                total_pages = int(response.headers.get("X-WP-TotalPages", "1") or "1")
            records.extend(data)
            if progress_label:
                print(
                    f"{progress_label}: page {page}/{total_pages}, {len(records)} records",
                    file=sys.stderr,
                )
            if max_records and len(records) >= max_records:
                return records[:max_records]
            page += 1
            time.sleep(0.08)
        return records


def strip_html(value: str | None) -> str:
    if not value:
        return ""
    if "<" not in value and "&" not in value:
        return re.sub(r"\s+", " ", value).strip()
    warnings.filterwarnings("ignore", category=MarkupResemblesLocatorWarning)
    soup = BeautifulSoup(value, "html.parser")
    for tag in soup(["script", "style", "noscript"]):
        tag.decompose()
    text = soup.get_text(" ", strip=True)
    return re.sub(r"\s+", " ", html.unescape(text)).strip()


def safe_title(value: dict[str, Any] | str | None) -> str:
    if isinstance(value, dict):
        value = value.get("rendered", "")
    return strip_html(str(value or "")) or "Untitled"


def safe_slug(value: str) -> str:
    slug = re.sub(r"[^a-zA-Z0-9._-]+", "-", value.strip().lower()).strip("-")
    return slug or "article"


def first_date_part(value: str | None) -> str:
    return (value or "unknown-date").split("T", 1)[0]


def yaml_dump(data: dict[str, Any]) -> str:
    return yaml.safe_dump(
        data,
        allow_unicode=True,
        sort_keys=False,
        default_flow_style=False,
        width=120,
    ).strip()


def extract_headings(content_html: str | None) -> list[dict[str, str]]:
    soup = BeautifulSoup(content_html or "", "html.parser")
    headings: list[dict[str, str]] = []
    for tag in soup.find_all(re.compile(r"^h[1-4]$")):
        text = strip_html(str(tag))
        if text:
            headings.append({"level": tag.name.upper(), "title": text})
    return headings[:60]


def add_image(images: list[dict[str, str]], seen: set[str], role: str, url: str | None, alt: str = "", caption: str = "") -> None:
    if not url or url.startswith("data:"):
        return
    normalized_url = html.unescape(url.strip())
    if not normalized_url or normalized_url in seen:
        return
    seen.add(normalized_url)
    images.append(
        {
            "role": role,
            "url": normalized_url,
            "alt": strip_html(alt),
            "caption": strip_html(caption),
        }
    )


def extract_images(post: dict[str, Any]) -> list[dict[str, str]]:
    images: list[dict[str, str]] = []
    seen: set[str] = set()

    yoast = post.get("yoast_head_json") or {}
    for image in yoast.get("og_image") or []:
        add_image(images, seen, "featured", image.get("url"), safe_title(post.get("title")))
    if yoast.get("twitter_image"):
        add_image(images, seen, "featured", yoast.get("twitter_image"), safe_title(post.get("title")))

    soup = BeautifulSoup(post.get("content", {}).get("rendered", "") or "", "html.parser")
    for img in soup.find_all("img"):
        src = img.get("src") or img.get("data-src")
        if not src and img.get("srcset"):
            src = img.get("srcset", "").split(",", 1)[0].strip().split(" ", 1)[0]
        if src:
            src = urljoin(post.get("link", PERCONA_BLOG_URL), src)
        parent_figure = img.find_parent("figure")
        caption = ""
        if parent_figure:
            caption_tag = parent_figure.find("figcaption")
            caption = strip_html(str(caption_tag)) if caption_tag else ""
        add_image(images, seen, "content", src, img.get("alt", ""), caption)
    return images


def image_kind(image: dict[str, str]) -> str:
    haystack = " ".join([image.get("url", ""), image.get("alt", ""), image.get("caption", "")]).lower()
    if re.search(r"\b(graph|chart|diagram|benchmark|latency|throughput|qps|dashboard|metric)\b", haystack):
        return "graph_or_chart"
    return "image"


def fetch_terms(client: PerconaClient) -> tuple[dict[int, dict[str, Any]], dict[int, dict[str, Any]]]:
    categories = {
        int(term["id"]): term
        for term in client.fetch_paginated("/categories", {"_fields": TERM_FIELDS, "orderby": "id", "order": "asc"})
    }
    tags = {
        int(term["id"]): term
        for term in client.fetch_paginated("/tags", {"_fields": TERM_FIELDS, "orderby": "id", "order": "asc"})
    }
    return categories, tags


def fetch_author(client: PerconaClient, author_id: int) -> dict[str, Any]:
    try:
        data, _ = client.get_json(f"/users/{author_id}", {"_fields": AUTHOR_FIELDS})
    except RuntimeError as exc:
        print(f"author:{author_id}: unavailable ({exc})", file=sys.stderr)
        return {"id": author_id, "name": "", "slug": "", "link": "", "url": "", "description": ""}
    if not isinstance(data, dict):
        return {"id": author_id, "name": "", "slug": "", "link": "", "url": "", "description": ""}
    return data


def author_from_post(post: dict[str, Any], authors: dict[int, dict[str, Any]]) -> dict[str, Any]:
    author_id = int(post.get("author") or 0)
    author = dict(authors.get(author_id, {}))
    if author.get("name"):
        return author

    yoast = post.get("yoast_head_json") or {}
    if yoast.get("author"):
        author["name"] = yoast["author"]

    schema = yoast.get("schema") or {}
    for graph_item in schema.get("@graph", []) if isinstance(schema, dict) else []:
        if not isinstance(graph_item, dict):
            continue
        schema_author = graph_item.get("author")
        if isinstance(schema_author, dict) and schema_author.get("name"):
            author["name"] = schema_author["name"]
            if schema_author.get("url"):
                author["link"] = schema_author["url"]
            break

    if not author.get("name"):
        author["name"] = f"Percona author {author_id}" if author_id else "Percona"
    author.setdefault("slug", "")
    author.setdefault("link", "")
    author.setdefault("url", "")
    author.setdefault("description", "")
    return author


def merge_posts(
    posts_by_id: dict[int, dict[str, Any]],
    matched_topics: dict[int, set[str]],
    matched_filters: dict[int, set[str]],
    posts: list[dict[str, Any]],
    topic: str,
    filter_label: str,
) -> None:
    for post in posts:
        post_id = int(post["id"])
        posts_by_id.setdefault(post_id, post)
        matched_topics[post_id].add(topic)
        matched_filters[post_id].add(filter_label)


def fetch_posts(
    client: PerconaClient,
    max_records: int = 0,
) -> tuple[dict[int, dict[str, Any]], dict[int, set[str]], dict[int, set[str]]]:
    posts_by_id: dict[int, dict[str, Any]] = {}
    matched_topics: dict[int, set[str]] = defaultdict(set)
    matched_filters: dict[int, set[str]] = defaultdict(set)

    for item in TOPIC_FILTERS:
        param_name = "categories" if item["type"] == "category" else "tags"
        filter_label = f"{item['type']}:{item['slug']}:{item['id']}"
        params = {
            param_name: item["id"],
            "_fields": POST_FIELDS,
            "orderby": "date",
            "order": "desc",
        }
        remaining = max(0, max_records - len(posts_by_id)) if max_records else 0
        posts = client.fetch_paginated("/posts", params, max_records=remaining, progress_label=filter_label)
        print(f"{filter_label}: {len(posts)} posts", file=sys.stderr)
        merge_posts(posts_by_id, matched_topics, matched_filters, posts, item["topic"], filter_label)
        if max_records and len(posts_by_id) >= max_records:
            return posts_by_id, matched_topics, matched_filters

    for item in SEARCH_FILTERS:
        filter_label = f"search:{safe_slug(item['query'])}"
        params = {
            "search": item["query"],
            "_fields": POST_FIELDS,
            "orderby": "date",
            "order": "desc",
        }
        remaining = max(0, max_records - len(posts_by_id)) if max_records else 0
        posts = client.fetch_paginated("/posts", params, max_records=remaining, progress_label=filter_label)
        print(f"{filter_label}: {len(posts)} posts", file=sys.stderr)
        merge_posts(posts_by_id, matched_topics, matched_filters, posts, item["topic"], filter_label)
        if max_records and len(posts_by_id) >= max_records:
            return posts_by_id, matched_topics, matched_filters

    return posts_by_id, matched_topics, matched_filters


def article_path(base_dir: Path, post: dict[str, Any]) -> Path:
    date_part = first_date_part(post.get("date"))
    year, month = (date_part.split("-") + ["00", "00"])[:2]
    filename = f"{date_part}-{safe_slug(post.get('slug') or safe_title(post.get('title')))}.md"
    return base_dir / "articles" / year / month / filename


def write_curator(author_dir: Path) -> None:
    author_dir.mkdir(parents=True, exist_ok=True)
    path = author_dir / f"{CURATOR['slug']}.md"
    front_matter = {
        "name": CURATOR["name"],
        "slug": CURATOR["slug"],
        "role": CURATOR["role"],
        "source_url": CURATOR["source_url"],
        "article_count_source": CURATOR["article_count_source"],
        "links": CURATOR["links"],
    }
    body = [
        "---",
        yaml_dump(front_matter),
        "---",
        "",
        f"# {CURATOR['name']}",
        "",
        CURATOR["bio"],
        "",
        "## Liens",
        "",
    ]
    for label, url in CURATOR["links"].items():
        body.append(f"- {label}: [{url}]({url})")
    body.append("")
    path.write_text("\n".join(body), encoding="utf-8")


def write_article(
    output_root: Path,
    post: dict[str, Any],
    categories: dict[int, dict[str, Any]],
    tags: dict[int, dict[str, Any]],
    authors: dict[int, dict[str, Any]],
    topics: set[str],
    filters: set[str],
) -> Path:
    title = safe_title(post.get("title"))
    author = author_from_post(post, authors)
    category_terms = [categories[term_id] for term_id in post.get("categories", []) if term_id in categories]
    tag_terms = [tags[term_id] for term_id in post.get("tags", []) if term_id in tags]
    headings = extract_headings(post.get("content", {}).get("rendered", ""))
    images = extract_images(post)
    excerpt = strip_html(post.get("excerpt", {}).get("rendered", ""))
    if len(excerpt) > 900:
        excerpt = excerpt[:897].rstrip() + "..."

    path = article_path(output_root, post)
    path.parent.mkdir(parents=True, exist_ok=True)

    front_matter = {
        "title": title,
        "source": {
            "name": "Percona Blog",
            "url": post.get("link"),
            "post_id": post.get("id"),
        },
        "source_author": {
            "name": author.get("name", ""),
            "slug": author.get("slug", ""),
            "url": author.get("link") or author.get("url") or "",
            "website": author.get("url") or "",
        },
        "published_at": post.get("date"),
        "published_at_gmt": post.get("date_gmt"),
        "modified_at": post.get("modified"),
        "modified_at_gmt": post.get("modified_gmt"),
        "curator": {
            "name": CURATOR["name"],
            "slug": CURATOR["slug"],
            "source_url": CURATOR["source_url"],
        },
        "matched_topics": sorted(topics),
        "matched_filters": sorted(filters),
        "categories": [term["name"] for term in category_terms],
        "category_slugs": [term["slug"] for term in category_terms],
        "tags": [term["name"] for term in tag_terms],
        "tag_slugs": [term["slug"] for term in tag_terms],
        "featured_image_url": images[0]["url"] if images else "",
        "image_count": len(images),
        "graph_or_chart_count": sum(1 for image in images if image_kind(image) == "graph_or_chart"),
        "content_import": "metadata_excerpt_headings_and_source_image_urls_only",
        "copyright_notice": "Percona All Rights Reserved; full article text and local image copies are not reproduced.",
    }

    body = [
        "---",
        yaml_dump(front_matter),
        "---",
        "",
        f"# {title}",
        "",
        f"Source: [Percona Blog]({post.get('link')})",
        "",
        f"Auteur source: [{author.get('name', 'Percona')}]({author.get('link') or author.get('url') or post.get('link')})",
        "",
        f"Publication: {post.get('date', '')}",
        "",
        f"Curateur PmaControl: [{CURATOR['name']}](../../../authors/{CURATOR['slug']}.md)",
        "",
        "## Note de droits",
        "",
        (
            "Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections "
            "detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales "
            "des images restent sur la source Percona."
        ),
        "",
    ]

    if excerpt:
        body.extend(["## Extrait public", "", excerpt, ""])

    if headings:
        body.extend(["## Structure detectee", ""])
        for heading in headings:
            body.append(f"- {heading['level']}: {heading['title']}")
        body.append("")

    if images:
        body.extend(["## Images et graphiques reperes", ""])
        for image in images:
            label = image["alt"] or image["caption"] or Path(image["url"].split("?", 1)[0]).name
            kind = image_kind(image)
            body.append(f"- {image['role']} / {kind}: [{label}]({image['url']})")
            if image.get("caption"):
                body.append(f"  Caption: {image['caption']}")
        body.append("")

    if author.get("description"):
        body.extend(["## Auteur source", "", strip_html(author.get("description", "")), ""])

    path.write_text("\n".join(body), encoding="utf-8")
    return path


def write_indexes(
    output_root: Path,
    generated: list[dict[str, Any]],
    topics_index: dict[str, list[dict[str, Any]]],
    categories: dict[int, dict[str, Any]],
    tags: dict[int, dict[str, Any]],
) -> None:
    output_root.mkdir(parents=True, exist_ok=True)
    topics_dir = output_root / "topics"
    data_dir = output_root / "_data"
    topics_dir.mkdir(parents=True, exist_ok=True)
    data_dir.mkdir(parents=True, exist_ok=True)

    by_date = sorted(generated, key=lambda item: item["published_at"] or "", reverse=True)
    index_lines = [
        "# Import Percona Blog",
        "",
        "Fiches de veille generees depuis le blog Percona.",
        "",
        "Les articles complets et les images ne sont pas copies localement: chaque fiche conserve les metadonnees,",
        "un extrait public, les titres de sections detectes et les URLs sources des visuels.",
        "",
        f"- Source: [{PERCONA_BLOG_URL}]({PERCONA_BLOG_URL})",
        f"- Reference de format PmaControl: [{PMACONTROL_REFERENCE_POST_URL}]({PMACONTROL_REFERENCE_POST_URL})",
        f"- Curateur: [{CURATOR['name']}](authors/{CURATOR['slug']}.md)",
        f"- Date d'import: {dt.datetime.now(dt.timezone.utc).isoformat()}",
        f"- Nombre de fiches: {len(by_date)}",
        "",
        "## Themes",
        "",
    ]
    for topic in sorted(topics_index):
        index_lines.append(f"- [{topic}](topics/{safe_slug(topic)}.md) ({len(topics_index[topic])})")
    index_lines.extend(["", "## Articles", ""])
    for item in by_date:
        index_lines.append(
            f"- {item['published_at'][:10]} - [{item['title']}]({item['path']}) "
            f"({', '.join(item['topics'])})"
        )
    index_lines.append("")
    (output_root / "index.md").write_text("\n".join(index_lines), encoding="utf-8")

    for topic, items in sorted(topics_index.items()):
        topic_lines = [f"# {topic}", "", f"{len(items)} fiches.", ""]
        for item in sorted(items, key=lambda record: record["published_at"] or "", reverse=True):
            topic_lines.append(f"- {item['published_at'][:10]} - [{item['title']}](../{item['path']})")
        topic_lines.append("")
        (topics_dir / f"{safe_slug(topic)}.md").write_text("\n".join(topic_lines), encoding="utf-8")

    manifest = {
        "source": PERCONA_BLOG_URL,
        "generated_at": dt.datetime.now(dt.timezone.utc).isoformat(),
        "article_count": len(generated),
        "topics": {topic: len(items) for topic, items in sorted(topics_index.items())},
        "topic_filters": TOPIC_FILTERS,
        "search_filters": SEARCH_FILTERS,
        "categories_loaded": len(categories),
        "tags_loaded": len(tags),
        "copyright_policy": "metadata/excerpt/headings/source image URLs only; no full articles and no local image copies",
    }
    (data_dir / "import_manifest.yml").write_text(yaml_dump(manifest) + "\n", encoding="utf-8")
    with (data_dir / "articles.jsonl").open("w", encoding="utf-8") as stream:
        for item in by_date:
            stream.write(json.dumps(item, ensure_ascii=False, sort_keys=True) + "\n")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--output",
        default=str(Path(__file__).resolve().parents[1] / "percona"),
        help="Output directory for generated Markdown files.",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=0,
        help="Limit generated articles after fetching and de-duplication; useful for tests.",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    output_root = Path(args.output).resolve()

    client = PerconaClient()
    print("Loading category and tag maps", file=sys.stderr)
    categories, tags = fetch_terms(client)

    print("Loading posts from configured filters", file=sys.stderr)
    posts_by_id, matched_topics, matched_filters = fetch_posts(client, max_records=args.limit)
    posts = sorted(posts_by_id.values(), key=lambda post: post.get("date") or "", reverse=True)

    author_ids = sorted({int(post.get("author") or 0) for post in posts if post.get("author")})
    print(f"Loading {len(author_ids)} authors", file=sys.stderr)
    authors = {author_id: fetch_author(client, author_id) for author_id in author_ids}

    write_curator(output_root / "authors")

    generated: list[dict[str, Any]] = []
    topics_index: dict[str, list[dict[str, Any]]] = defaultdict(list)
    for post in posts:
        post_id = int(post["id"])
        path = write_article(
            output_root,
            post,
            categories,
            tags,
            authors,
            matched_topics[post_id],
            matched_filters[post_id],
        )
        relative_path = path.relative_to(output_root).as_posix()
        item = {
            "id": post_id,
            "title": safe_title(post.get("title")),
            "source_url": post.get("link"),
            "path": relative_path,
            "published_at": post.get("date") or "",
            "topics": sorted(matched_topics[post_id]),
        }
        generated.append(item)
        for topic in matched_topics[post_id]:
            topics_index[topic].append(item)

    write_indexes(output_root, generated, topics_index, categories, tags)
    print(f"Generated {len(generated)} Markdown notes in {output_root}", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
