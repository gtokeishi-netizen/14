# Pages Directory Structure

このディレクトリは固定ページテンプレートを整理するためのフォルダー構造です。

## ディレクトリ構造

```
pages/
├── README.md              # このファイル - 構造説明
├── templates/             # 実際のページテンプレート
│   ├── page-about.php     # About Page (サービスについて)
│   ├── page-contact.php   # Contact Page (お問い合わせ)
│   ├── page-faq.php       # FAQ Page (よくある質問)
│   ├── page-privacy.php   # Privacy Policy (プライバシーポリシー)
│   └── page-terms.php     # Terms of Service (利用規約)
└── partials/              # 再利用可能なパーシャルファイル
    └── (今後追加予定)
```

## WordPressテンプレート階層の維持

WordPressのテンプレート階層を維持するため、以下の構造を採用しています：

1. **ルートディレクトリ**: WordPressが認識する `page-*.php` ファイル
2. **pages/templates/**: 実際のテンプレートコード
3. **pages/partials/**: 共通パーシャル（今後使用予定）

### 動作の仕組み

```php
// ルートの page-about.php
$template_path = get_template_directory() . '/pages/templates/page-about.php';
if (file_exists($template_path)) {
    include $template_path; // 実際のテンプレートを読み込み
}
```

## 利点

1. **整理された構造**: ページテンプレートが一箇所に集約
2. **WordPress互換性**: テンプレート階層を完全に維持
3. **保守性向上**: ファイルの管理が容易
4. **拡張性**: 新しいページテンプレートの追加が簡単
5. **再利用性**: パーシャルファイルで共通コンポーネントを管理

## ファイル管理ルール

### 新しい固定ページの追加
1. `pages/templates/page-新しいページ.php` を作成
2. ルートディレクトリに `page-新しいページ.php` を作成（インクルード用）

### パーシャルファイルの使用
```php
// pages/templates 内でパーシャルを読み込む場合
include get_template_directory() . '/pages/partials/共通パーツ.php';
```

## 作成日時
2025-10-05 - 固定ページ構造整理プロジェクト