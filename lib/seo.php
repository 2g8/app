<?php
// 本脚本依赖 melbahja/seo，如需使用请按如下方式安装
// composer require melbahja/seo

use Melbahja\Seo\Schema;
use Melbahja\Seo\Schema\Thing;
use Melbahja\Seo\MetaTags;
use Melbahja\Seo\Sitemap;
use Melbahja\Seo\Ping;
use Melbahja\Seo\Indexing;

// -----------------------------------------------------------------------
// ===== 网页MetaTags =====
// -----------------------------------------------------------------------
// 一般网页包含 title,description等
// -----------------------------------------------------------------------
function seo_metatags($metadata = []){
    if(!isset($metadata['title'])) $metadata['title'] = 'Quảng cáo rao vặt, Thông tin cuộc sống Việt Nam - 9898555.com';
    if(!isset($metadata['keywords'])) $metadata['keywords'] = '9898555,9898555.com,Tuyển dụng, trang web tuyển dụng, thông tin tuyển dụng, việc bán thời gian, trang web bán thời gian, thông tin bán thời gian, thông tin tuyển dụng, việc làm, thông tin tuyển dụng, bất động sản, mua bán bất động sản, cho thuê bất động sản, sử dụng, chợ đã qua sử dụng, xe đã qua sử dụng, mua bán xe đã qua sử dụng, tuyên truyền của doanh nghiệp';
    if(!isset($metadata['description'])) $metadata['description'] = 'Tuyển dụng, việc làm bán thời gian, bất động sản, mua bán, ô tô cũ, quảng cáo thương mại, phát hành và quản lý quảng cáo tại Hồ Chí Minh, Hà Nội và các khu vực khác tại Việt Nam.';
    $metatags = new MetaTags();
    return $metatags
        ->title($metadata['title'])
        ->meta('keywords', $metadata['keywords'])
        ->description($metadata['description'])
        ->image('https://9898555.com/assets/img/android-chrome-512x512.png');
        //->mobile('https://9898555.com/?device=mobile')
        //->canonical('https://9898555.com');
}

// -----------------------------------------------------------------------
// ===== 生成sitemap =====
// -----------------------------------------------------------------------
// sitemap是搜索引擎索引的文件,
// maplist example
// [
//  'blog'=>[
//        ['loc'=>'/blog','freq'=>'daily','priority'=>'0.8','lastmod'=>'2019-03-01'],
//        ['loc'=>'/blog/my-new-article','freq'=>'monthly','priority'=>'0.8']
//    ]
// ]
// -----------------------------------------------------------------------
function seo_sitemap(){
    // 索引文件，<save_path>/sitemap.xml
    $sitemap = new Sitemap('https://9898555.com', ['save_path' => APP_PATH]);
    $sitemap->setIndexName('data/sitemap.xml');
    // 子频道具体文件
    $sitemap->links('data/job.xml', function($map)
    {
        //从数据库读取
        $mp = new model_posts();
        $posts = $mp->gets(['cate'=>'job','is_open'=>1,'order'=>'postid desc','limit'=>500]);
        //循环写入
        foreach ($posts as $post)
        {
            $map->loc('/pub/detail/'.id62($post['postid']).'/'.makeUrlSlug($post['title']).'.htm')->freq('monthly')->priority('0.8')->lastMod($post['update_time']);
        }
    });
    //这是频道2
    $sitemap->links('data/sale.xml', function($map)
    {
        //从数据库读取
        $mp = new model_posts();
        $posts = $mp->gets(['cate'=>'sale','is_open'=>1,'order'=>'postid desc','limit'=>500]);
        //循环写入
        foreach ($posts as $post)
        {
            $map->loc('/pub/detail/'.id62($post['postid']).'/'.makeUrlSlug($post['title']).'.htm')->freq('monthly')->priority('0.8')->lastMod($post['update_time']);
        }
    });
    //这是频道3
    $sitemap->links('data/house.xml', function($map)
    {
        //从数据库读取
        $mp = new model_posts();
        $posts = $mp->gets(['cate'=>'house','is_open'=>1,'order'=>'postid desc','limit'=>500]);
        //循环写入
        foreach ($posts as $post)
        {
            $map->loc('/pub/detail/'.id62($post['postid']).'/'.makeUrlSlug($post['title']).'.htm')->freq('monthly')->priority('0.8')->lastMod($post['update_time']);
        }
    });

    $sitemap->save();
}

// -----------------------------------------------------------------------
// ===== 搜索引擎提交 =====
// -----------------------------------------------------------------------
// 包含两种方式 ping 和 indexnow
// ping方法提交sitemap
// <searchengine_URL>/ping?sitemap=sitemap_url
// indexnow方法提交URL sets
// <searchengine>/indexnow?url=http://www.example.com/product.html&key=d54045c905254a5fb5916afef8f75897&keyLocation=http://www.example.com/myIndexNowKey63638.txt
//    POST /indexnow HTTP/1.1
//    Content-Type: application/json; charset=utf-8
//    Host: <searchengine>
//    {
//        "host": "www.example.com",
//      "key": "d54045c905254a5fb5916afef8f75897",
//      "keyLocation": "https://www.example.com/myIndexNowKey63638.txt",
//      "urlList": [
//        "https://www.example.com/url1",
//        "https://www.example.com/folder/url2",
//        "https://www.example.com/url3"
//    ]
//    }
// -----------------------------------------------------------------------
function seo_ping(){
    $ping = new Ping;
    $ping->send('https://9898555.com/data/sitemap.xml');
}

// 各家搜索引擎对indexing的支持不同
// 百度Token获取地址： https://ziyuan.baidu.com/linksubmit/index
// 必应Token获取地址：https://docs.microsoft.com/en-us/bingwebmaster/getting-access#using-api-key
// 谷歌JSON私钥获取：https://developers.google.cn/search/apis/indexing-api/v3/quickstart?hl=zh-cn&authuser=0
// 注意：谷歌只接受  JobPosting 或 BroadcastEvent 并且需要翻墙，每次最大100条
function seo_indexing(){
    $indexer = new Indexing('www.9898555.com', [
        'bing.com' => '361dccf73a6a458c6ad16003c9ad6ad0',//md5 ding.98
        'yandex.com' => '361dccf73a6a458c6ad16003c9ad6ad0',
    ]);
    $indexer->indexUrls(['https://www.example.com/page']); //批量提交URL数组
}

// -----------------------------------------------------------------------
// ===== 网页schema =====
// -----------------------------------------------------------------------
//一般网页包含 组织信息（网站所有者），产品信息（卖产品的话），文章信息（新闻类）
// -----------------------------------------------------------------------
function seo_schemas($array_schemas = []){
    return new Schema($array_schemas);
}

function seo_format($schema){
    return json_encode($schema, JSON_PRETTY_PRINT);
}

// 工作类，根据谷歌格式
// 参考地址：https://developers.google.com/search/docs/appearance/structured-data/job-posting?hl=zh-cn
function seo_job($data){
    $salary = new Thing('MonetaryAmount', [

    ]);
    return new Thing('JobPosting',[
        'title'                 => $data['title'],
        'description'           => $data['desc'],
        'datePosted'            => $data['datePosted'],
        'validThrough'          => $data['validThrough'],
        'hiringOrganization'    => schema_organization(),
        'employmentType'        => $data['type'],//"CONTRACTOR",
        'jobLocation'           => schema_place(),
        'baseSalary'            => $salary
    ]);
}

// 房屋或者土地出租，根据schema.org
// 网址：https://schema.org/RentAction
function seo_rentaction($data){
    $location = new Thing('Place', [

    ]);
}

function schema_person($data){
    return new Thing('Person', [
        'name'          => 'Jane Doe',
        'jobTitle'          => 'Professor',
        'telephone'          => '(425) 123-4567',
        'email'          => 'mailto:jane-doe@xyz.edu',
        'image'          => 'janedoe.jpg',
        'url'          => 'http://www.janedoe.com',
        'address'          => schema_postaladdress(),
    ]);
}

function schema_organization($data){
    return new Thing('Organization', [
        'url'          => 'https://example.com',
        'logo'         => 'https://example.com/logo.png',
        'contactPoint' => new Thing('ContactPoint', [
            'telephone' => '+1-000-555-1212',
            'contactType' => 'customer service'
        ])
    ]);
}

function schema_place($data){
    return new Thing("Place", [

    ]);
}

function schema_postaladdress($data){
    return new Thing("PostalAddress", [
        'addressLocality'   => 'Seattle',
        'addressRegion'   => 'WA',
        'postalCode'   => '98052',
        'streetAddress'   => '20341 Whitworth Institute 405 N. Whitworth'
    ]);
}

function schema_webpage($data){
    return new Thing("WebPage", [
        '@id' => "https://example.com/product/#webpage",
        'url' => "https://example.com/product",
        'name' => 'Foo Bar',
    ]);
}

