<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Directory;
use App\Entity\Post;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends AbstractFixture implements DependentFixtureInterface
{
    private const SECRET = [
        'name' => 'invisionapp',
        'login' => 'list',
        'pass' => 'passwort',
        'note' => 'Authorisation ...',
        'attachments' => [
            [
                'id' => '25769c6c-d34d-4bfe-ba98-e0ee856f3e7a',
                'name' => 'logo.jpg',
                'raw' => 'data:application/pdf;base64,JVBERi0xLjQNJeLjz9MNCjYgMCBvYmo8PC9IWzUxNiAxNDFdL0xpbmVhcml6ZWQgMS9FIDQ1MzQvTCA4MzU3L04gMS9PIDkvVCA4MTkxPj4NZW5kb2JqDSAgICAgICAgICAgICAgICAgICAgICAgICAgDQp4cmVmDQo2IDExDQowMDAwMDAwMDE2IDAwMDAwIG4NCjAwMDAwMDA2NTcgMDAwMDAgbg0KMDAwMDAwMDUxNiAwMDAwMCBuDQowMDAwMDAwNzMzIDAwMDAwIG4NCjAwMDAwMDA4NjAgMDAwMDAgbg0KMDAwMDAwMDk3MCAwMDAwMCBuDQowMDAwMDAxMzI3IDAwMDAwIG4NCjAwMDAwMDE1NDggMDAwMDAgbg0KMDAwMDAwMTU4MiAwMDAwMCBuDQowMDAwMDA0MjUxIDAwMDAwIG4NCjAwMDAwMDQ0NTggMDAwMDAgbg0KdHJhaWxlcg0KPDwvU2l6ZSAxNy9QcmV2IDgxODEvUm9vdCA3IDAgUi9JbmZvIDUgMCBSL0lEWzxkYjc3NzVjY2UyMjdmNmIzMGM0NDBkZjQyMjFkYzM5MD48YjBiMzYzOGRlYTU2ODg0Njg5NzQ2MGRiNTBmMzA1ZTg+XT4+DQpzdGFydHhyZWYNCjANCiUlRU9GDQogICAgICAgICAgICAgICAgIA0KOCAwIG9iajw8L0xlbmd0aCA2NC9GaWx0ZXIvRmxhdGVEZWNvZGUvTCA3NS9TIDM4Pj5zdHJlYW0NCnjaYmBg4GBgYApgAAK+mwyogAmIWRg4GhiRxDigmIFBiYGH9YKPiMFuBi5vrgtaICGgQkFNqEYLgAADAN+lBf0NCmVuZHN0cmVhbQ1lbmRvYmoNNyAwIG9iajw8L1BhZ2VzIDMgMCBSL1R5cGUvQ2F0YWxvZy9QYWdlTGFiZWxzIDEgMCBSL01ldGFkYXRhIDQgMCBSPj4NZW5kb2JqDTkgMCBvYmo8PC9Db250ZW50cyAxMiAwIFIvVHlwZS9QYWdlL1BhcmVudCAzIDAgUi9Sb3RhdGUgMC9NZWRpYUJveFswIDAgNjEyIDc5Ml0vQ3JvcEJveFswIDAgNjEyIDc5Ml0vUmVzb3VyY2VzIDEwIDAgUj4+DWVuZG9iag0xMCAwIG9iajw8L0NvbG9yU3BhY2U8PC9DczYgMTMgMCBSPj4vRm9u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ZS9TTSAwLjAyL29wIGZhbHNlL09QTSAxPj4NZW5kb2JqDTEgMCBvYmo8PC9OdW1zWzAgMiAwIFJdPj4NZW5kb2JqDTIgMCBvYmo8PC9TL0Q+Pg1lbmRvYmoNMyAwIG9iajw8L0NvdW50IDEvS2lkc1s5IDAgUl0vVHlwZS9QYWdlcz4+DWVuZG9iag00IDAgb2JqPDwvTGVuZ3RoIDMyNjEvVHlwZS9NZXRhZGF0YS9TdWJ0eXBlL1hNTD4+c3RyZWFtDQo8P3hwYWNrZXQgYmVnaW49J++7vycgaWQ9J1c1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCc/Pgo8P2Fkb2JlLXhhcC1maWx0ZXJzIGVzYz0iQ1JMRiI/Pg0KPHg6eG1wbWV0YSB4bWxuczp4PSdhZG9iZTpuczptZXRhLycgeDp4bXB0az0nWE1QIHRvb2xraXQgMi45LjEtMTMsIGZyYW1ld29yayAxLjYnPg0KPHJkZjpSREYgeG1sbnM6cmRmPSdodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjJyB4bWxuczppWD0naHR0cDovL25zLmFkb2JlLmNvbS9pWC8xLjAvJz4NCjxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSd1dWlkOjVjYTU5OTU5LWY4Y2UtNGY1Mi04ZGEwLTE4N2EzOTFjNWIxOScgeG1sbnM6cGRmPSdodHRwOi8vbnMuYWRvYmUuY29tL3BkZi8xLjMvJyBwZGY6UHJvZHVjZXI9J0Fjcm9iYXQgRGlzdGlsbGVyIDYuMCAoV2luZG93cyknPjwvcmRmOkRlc2NyaXB0aW9uPg0KPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9J3V1aWQ6NWNhNTk5NTktZjhjZS00ZjUyLThkYTAtMTg3YTM5MWM1YjE5JyB4bWxuczp4YXA9J2h0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8nIHhhcDpDcmVhdGVEYXRlPScyMDA2LTAzLTA2VDE1OjA2OjMzLTA1OjAwJyB4YXA6Q3JlYXRvclRvb2w9J0Fkb2JlUFM1LmRsbCBWZXJzaW9uIDUuMi4yJyB4YXA6TW9kaWZ5RGF0ZT0nMjAwNi0wMy0wNlQxNTowNjozMy0wNTowMCc+PC9yZGY6RGVzY3JpcHRpb24+DQo8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0ndXVpZDo1Y2E1OTk1OS1mOGNlLTRmNTItOGRhMC0xODdhMzkxYzViMTknIHhtbG5zOnhhcE1NPSdodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vJyB4YXBNTTpEb2N1bWVudElEPSd1dWlkOmZmM2RjZmQxLTIzZmEtNDc2Zi04MzlhLTNlNWNhZTJkYTJlYicvPg0KPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9J3V1aWQ6NWNhNTk5NTktZjhjZS00ZjUyLThkYTAtMTg3YTM5MWM1YjE5JyB4bWxuczpkYz0naHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8nIGRjOmZvcm1hdD0nYXBwbGljYXRpb24vcGRmJz48ZGM6dGl0bGU+PHJkZjpBbHQ+PHJkZjpsaSB4bWw6bGFuZz0neC1kZWZhdWx0Jz5NaWNyb3NvZnQgV29yZCAtIERvY3VtZW50MjwvcmRmOmxpPjwvcmRmOkFsdD48L2RjOnRpdGxlPjwvcmRmOkRlc2NyaXB0aW9uPg0KPC9yZGY6UkRGPg0KPC94OnhtcG1ldGE+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9J3cnPz4NCmVuZHN0cmVhbQ1lbmRvYmoNNSAwIG9iajw8L01vZERhdGUoRDoyMDA2MDMwNjE1MDYzMy0wNScwMCcpL0NyZWF0aW9uRGF0ZShEOjIwMDYwMzA2MTUwNjMzLTA1JzAwJykvVGl0bGUoTWljcm9zb2Z0IFdvcmQgLSBEb2N1bWVudDIpL0NyZWF0b3IoQWRvYmVQUzUuZGxsIFZlcnNpb24gNS4yLjIpL1Byb2R1Y2VyKEFjcm9iYXQgRGlzdGlsbGVyIDYuMCBcKFdpbmRvd3NcKSk+Pg1lbmRvYmoNeHJlZg0KMCA2DQowMDAwMDAwMDAwIDY1NTM1IGYNCjAwMDAwMDQ1MzQgMDAwMDAgbg0KMDAwMDAwNDU2NyAwMDAwMCBuDQowMDAwMDA0NTkwIDAwMDAwIG4NCjAwMDAwMDQ2NDAgMDAwMDAgbg0KMDAwMDAwNzk3NyAwMDAwMCBuDQp0cmFpbGVyDQo8PC9TaXplIDY+Pg0Kc3RhcnR4cmVmDQoxMTYNCiUlRU9GDQo1IDAgb2JqDTw8IA0vTW9kRGF0ZSAoRDoyMDA2MDMwNjE1MTIzMy0wNScwMCcpDS9DcmVhdGlvbkRhdGUgKEQ6MjAwNjAzMDYxNTA2MzMtMDUnMDAnKQ0vVGl0bGUgKEJsYW5rIFBERiBEb2N1bWVudCkNL0NyZWF0b3IgKEFkb2JlUFM1LmRsbCBWZXJzaW9uIDUuMi4yKQ0vUHJvZHVjZXIgKEFjcm9iYXQgRGlzdGlsbGVyIDYuMCBcKFdpbmRvd3NcKSkNL0F1dGhvciAoRGVwYXJ0bWVudCBvZiBKdXN0aWNlIFwoRXhlY3V0aXZlIE9mZmljZSBvZiBJbW1pZ3JhdGlvbiBSZXZpZXdcKSkNPj4gDWVuZG9iag03IDAgb2JqDTw8IA0vUGFnZXMgMyAwIFIgDS9UeXBlIC9DYXRhbG9nIA0vUGFnZUxhYmVscyAxIDAgUiANL01ldGFkYXRhIDE3IDAgUiANPj4gDWVuZG9iag0xNyAwIG9iag08PCAvVHlwZSAvTWV0YWRhdGEgL1N1YnR5cGUgL1hNTCAvTGVuZ3RoIDIwMzMgPj4gDXN0cmVhbQ0KPD94cGFja2V0IGJlZ2luPScnIGlkPSdXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQnIGJ5dGVzPScyMDMzJz8+Cgo8cmRmOlJERiB4bWxuczpyZGY9J2h0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMnCiB4bWxuczppWD0naHR0cDovL25zLmFkb2JlLmNvbS9pWC8xLjAvJz4KCiA8cmRmOkRlc2NyaXB0aW9uIGFib3V0PSd1dWlkOjVjYTU5OTU5LWY4Y2UtNGY1Mi04ZGEwLTE4N2EzOTFjNWIxOScKICB4bWxucz0naHR0cDovL25zLmFkb2JlLmNvbS9wZGYvMS4zLycKICB4bWxuczpwZGY9J2h0dHA6Ly9ucy5hZG9iZS5jb20vcGRmLzEuMy8nPgogIDxwZGY6UHJvZHVjZXI+QWNyb2JhdCBEaXN0aWxsZXIgNi4wIChXaW5kb3dzKTwvcGRmOlByb2R1Y2VyPgogIDxwZGY6Q3JlYXRpb25EYXRlPjIwMDYtMDMtMDZUMTU6MDY6MzMtMDU6MDA8L3BkZjpDcmVhdGlvbkRhdGU+CiAgPHBkZjpNb2REYXRlPjIwMDYtMDMtMDZUMTU6MTI6MzMtMDU6MDA8L3BkZjpNb2REYXRlPgogIDxwZGY6VGl0bGU+QmxhbmsgUERGIERvY3VtZW50PC9wZGY6VGl0bGU+CiAgPHBkZjpDcmVhdG9yPkFkb2JlUFM1LmRsbCBWZXJzaW9uIDUuMi4yPC9wZGY6Q3JlYXRvcj4KICA8cGRmOkF1dGhvcj5EZXBhcnRtZW50IG9mIEp1c3RpY2UgKEV4ZWN1dGl2ZSBPZmZpY2Ugb2YgSW1taWdyYXRpb24gUmV2aWV3KTwvcGRmOkF1dGhvcj4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKIDxyZGY6RGVzY3JpcHRpb24gYWJvdXQ9J3V1aWQ6NWNhNTk5NTktZjhjZS00ZjUyLThkYTAtMTg3YTM5MWM1YjE5JwogIHhtbG5zPSdodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvJwogIHhtbG5zOnhhcD0naHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyc+CiAgPHhhcDpDcmVhdGVEYXRlPjIwMDYtMDMtMDZUMTU6MDY6MzMtMDU6MDA8L3hhcDpDcmVhdGVEYXRlPgogIDx4YXA6Q3JlYXRvclRvb2w+QWRvYmVQUzUuZGxsIFZlcnNpb24gNS4yLjI8L3hhcDpDcmVhdG9yVG9vbD4KICA8eGFwOk1vZGlmeURhdGU+MjAwNi0wMy0wNlQxNToxMjozMy0wNTowMDwveGFwOk1vZGlmeURhdGU+CiAgPHhhcDpGb3JtYXQ+YXBwbGljYXRpb24vcGRmPC94YXA6Rm9ybWF0PgogIDx4YXA6VGl0bGU+CiAgIDxyZGY6QWx0PgogICAgPHJkZjpsaSB4bWw6bGFuZz0neC1kZWZhdWx0Jz5CbGFuayBQREYgRG9jdW1lbnQ8L3JkZjpsaT4KICAgPC9yZGY6QWx0PgogIDwveGFwOlRpdGxlPgogIDx4YXA6QXV0aG9yPkRlcGFydG1lbnQgb2YgSnVzdGljZSAoRXhlY3V0aXZlIE9mZmljZSBvZiBJbW1pZ3JhdGlvbiBSZXZpZXcpPC94YXA6QXV0aG9yPgogIDx4YXA6TWV0YWRhdGFEYXRlPjIwMDYtMDMtMDZUMTU6MTI6MzMtMDU6MDA8L3hhcDpNZXRhZGF0YURhdGU+CiA8L3JkZjpEZXNjcmlwdGlvbj4KCiA8cmRmOkRlc2NyaXB0aW9uIGFib3V0PSd1dWlkOjVjYTU5OTU5LWY4Y2UtNGY1Mi04ZGEwLTE4N2EzOTFjNWIxOScKICB4bWxucz0naHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLycKICB4bWxuczp4YXBNTT0naHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyc+CiAgPHhhcE1NOkRvY3VtZW50SUQ+dXVpZDpmZjNkY2ZkMS0yM2ZhLTQ3NmYtODM5YS0zZTVjYWUyZGEyZWI8L3hhcE1NOkRvY3VtZW50SUQ+CiA8L3JkZjpEZXNjcmlwdGlvbj4KCiA8cmRmOkRlc2NyaXB0aW9uIGFib3V0PSd1dWlkOjVjYTU5OTU5LWY4Y2UtNGY1Mi04ZGEwLTE4N2EzOTFjNWIxOScKICB4bWxucz0naHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8nCiAgeG1sbnM6ZGM9J2h0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvJz4KICA8ZGM6Zm9ybWF0PmFwcGxpY2F0aW9uL3BkZjwvZGM6Zm9ybWF0PgogIDxkYzp0aXRsZT5CbGFuayBQREYgRG9jdW1lbnQ8L2RjOnRpdGxlPgogIDxkYzpjcmVhdG9yPkRlcGFydG1lbnQgb2YgSnVzdGljZSAoRXhlY3V0aXZlIE9mZmljZSBvZiBJbW1pZ3JhdGlvbiBSZXZpZXcpPC9kYzpjcmVhdG9yPgogPC9yZGY6RGVzY3JpcHRpb24+Cgo8L3JkZjpSREY+Cjw/eHBhY2tldCBlbmQ9J3InPz4NZW5kc3RyZWFtDWVuZG9iag14cmVmDTAgMSANMDAwMDAwMDAwMCA2NTUzNSBmDQo1IDEgDTAwMDAwMDgzNTcgMDAwMDAgbg0KNyAxIA0wMDAwMDA4NjQyIDAwMDAwIG4NCjE3IDEgDTAwMDAwMDg3MzIgMDAwMDAgbg0KdHJhaWxlcg08PA0vU2l6ZSAxOA0vSW5mbyA1IDAgUiANL1Jvb3QgNyAwIFIgDS9QcmV2IDExNiANL0lEWzxkYjc3NzVjY2UyMjdmNmIzMGM0NDBkZjQyMjFkYzM5MD48NGEyYjY4Y2ZjN2ExZWZmNTZmZWQ3NWFmNWQyOGJkYWM+XQ0+Pg1zdGFydHhyZWYNMTA4NTANJSVFT0YN',
            ],
        ],
    ];

    public function loadProd(ObjectManager $manager)
    {
        $ipopovList = $this->getRef(Directory::class, 'ipopovlist');
        $dspiridonovList = $this->getRef(Directory::class, 'dspiridonovlist');
        $posts = [];

        $posts['ipopovpost'] = new Post();
        $posts['ipopovpost']->setParentList($ipopovList);
        $posts['ipopovpost']->setSecret(self::SECRET);

        $posts['dspiridonovpost'] = new Post();
        $posts['dspiridonovpost']->setParentList($dspiridonovList);
        $posts['dspiridonovpost']->setSecret(self::SECRET);

        $this->save($posts, Post::class);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
