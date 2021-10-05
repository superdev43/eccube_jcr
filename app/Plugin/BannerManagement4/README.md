# キービジュアル管理プラグイン for EC-CUBE4
## 概要

トップページのキービジュアルを管理するプラグイン。  

## バナーの表示方法
### 自動で置き換える
プラグインの設定画面より自動で置き換えるを設定してください
### 手動で表示する
自動でうまくいかない場合はテンプレートにバナーを手動で埋め込んでください。

#### 置き換え前

```
<div class="item slick-slide"><img src="{{ asset('assets/img/top/img_hero_pc01.jpg') }}"></div>
<div class="item slick-slide"><img src="{{ asset('assets/img/top/img_hero_pc02.jpg') }}"></div>
<div class="item slick-slide"><img src="{{ asset('assets/img/top/img_hero_pc03.jpg') }}"></div>
```
#### 置き換え後

```
{{ include('@BannerManagement4/slides.twig', {}, ignore_missing = true) }}
```
