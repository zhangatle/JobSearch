
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml">
{% load staticfiles %}
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=emulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>详情|搜索引擎</title>
    <link href="{% static 'css/style.css' %}" rel="stylesheet" type="text/css" />
    <link href="{% static 'css/result.css' %}" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="container">
    <div id="hd" class="ue-clear">
        <a href="{% url 'index' %}"><div class="logo"></div></a>
        <div class="inputArea">
            <input type="text" class="searchInput" value="{{ key_words }}"/>
            <input type="button" class="searchButton" onclick="add_search()"/>
        </div>
    </div>
    <div class="nav">
        <ul class="searchList">
            <li class="searchItem {% ifequal s_type "article" %}current{% endifequal %}" data-type="article">伯乐文章</li>
            <li class="searchItem {% ifequal s_type "question" %}current{% endifequal %}" data-type="question">知乎问答</li>
            <li class="searchItem {% ifequal s_type "job" %}current{% endifequal %}" data-type="job">拉勾职位</li>
        </ul>
    </div>
    <div id="bd" class="ue-clear">
        <div id="main">
            <div class="sideBar">

                <div class="subfield">数据来源: </br>
                    实时已爬取数据统计</div>
                <ul class="subfieldContext">
                    <li>
                        <span class="name">伯乐在线</span>
                        <span class="unit">{{jobbole_count}}</span>
                    </li>
                    <li>
                        <span class="name">知乎</span>
                        <span class="unit">{{zhihu_count}}</span>
                    </li>
                    <li>
                        <span class="name">拉勾网</span>
                        <span class="unit">{{job_count}}</span>
                    </li>
                    <!--                     <li class="more">
                                            <a href="javascript:;">
                                                <span class="text">更多</span>
                                                <i class="moreIcon"></i>
                                            </a>
                                        </li> -->
                </ul>


                <div class="sideBarShowHide">
                    <a href="javascript:;" class="icon"></a>
                </div>
            </div>
            <div class="resultArea">
                <p class="resultTotal">
                    <span class="info">找到约&nbsp;<span class="totalResult">{{ total_nums }}</span>&nbsp;条结果(用时<span class="time">{{ last_seconds }}</span>秒)，共约<span class="totalPage">{{ page_nums }}</span>页</span>
                </p>
                <div class="resultList">
                    <div class="resultItem">
                        {% for hits in all_hits %}
                        <div class="itemHead">
                            <a href="{{ hits.url }}"  target="_blank" class="title">{% if s_type == "job"%}{% autoescape off %}{{ hits.title }}{% endautoescape %} | {% autoescape off %}{{ hits.company_name }}{% endautoescape %}
                                {% else %}{% autoescape off %}{{ hits.title }}{% endautoescape %}{% endif %}</a>
                            <span class="divsion">-</span>
                            <span class="fileType">
                                <span class="label">来源：</span>
                                <span class="value">{{ hits.source_site }}</span>
                            </span>
                            <span class="dependValue">
                                <span class="label">得分：</span>
                                <span class="value">{{ hits.score }}</span>
                            </span>
                        </div>
                        <div class="itemBody">{% autoescape off %}{{ hits.content }}{% endautoescape %}</div>
                        <div class="itemFoot">
                            <span class="info">
                                <label>网站：</label>
                                <span class="value">{{ hits.source_site }}</span>
                            </span>
                            <span class="info">
                                <label>发布时间：</label>
                                <span class="value">{% if s_type == "article" %}
                                 {{ hits.create_date }}
                                {% else %}
                                    {{ hits.create_date|slice:":-9" }}
                                {% endif %}
                                </span>
                            </span>
                        </div>
                        {% endfor %}
                    </div>
                </div>
                <!-- 分页 -->
                <div class="pagination ue-clear"></div>
                <!-- 相关搜索 -->
            </div>
            <div class="historyArea">
                <div class="hotSearch">
                    <h6>热门搜索</h6>
                    <ul class="historyList">
                        {% for search in topn_search %}
                        <li><a href="/search?q={{ search }}&s_type=article">{{ search }}</a></li>
                        {% endfor %}
                    </ul>
                </div>
                <div class="mySearch">
                    <h6>我的搜索</h6>
                    <ul class="historyList">

                    </ul>
                </div>
            </div>
        </div><!-- End of main -->
    </div><!--End of bd-->
</div>

<div id="foot">Copyright &copy;search.zhangatle.cn 版权所有  E-mail:search@zhangatle.cn</div>
</body>
<script type="text/javascript" src="{% static 'js/jquery.js' %}"></script>
<script type="text/javascript" src="{% static 'js/global.js' %}"></script>
<script type="text/javascript" src="{% static 'js/pagination.js' %}"></script>
<script type="text/javascript">
    var search_url = "{% url 'search' %}"

    $('.searchList').on('click', '.searchItem', function(){
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    $.each($('.subfieldContext'), function(i, item){
        $(this).find('li:gt(2)').hide().end().find('li:last').show();
    });

    function removeByValue(arr, val) {
        for(var i=0; i<arr.length; i++) {
            if(arr[i] == val) {
                arr.splice(i, 1);
                break;
            }
        }
    }
    $('.subfieldContext .more').click(function(e){
        var $more = $(this).parent('.subfieldContext').find('.more');
        if($more.hasClass('show')){

            if($(this).hasClass('define')){
                $(this).parent('.subfieldContext').find('.more').removeClass('show').find('.text').text('自定义');
            }else{
                $(this).parent('.subfieldContext').find('.more').removeClass('show').find('.text').text('更多');
            }
            $(this).parent('.subfieldContext').find('li:gt(2)').hide().end().find('li:last').show();
        }else{
            $(this).parent('.subfieldContext').find('.more').addClass('show').find('.text').text('收起');
            $(this).parent('.subfieldContext').find('li:gt(2)').show();
        }

    });

    $('.sideBarShowHide a').click(function(e) {
        if($('#main').hasClass('sideBarHide')){
            $('#main').removeClass('sideBarHide');
            $('#container').removeClass('sideBarHide');
        }else{
            $('#main').addClass('sideBarHide');
            $('#container').addClass('sideBarHide');
        }

    });
    var key_words = "{{ key_words }}"
    //分页
    $(".pagination").pagination({{ total_nums }}, {
        current_page :{{ page|add:'-1' }}, //当前页码
        items_per_page :10,
        display_msg :true,
        callback :pageselectCallback
    });
    function pageselectCallback(page_id, jq) {
        window.location.href=search_url+'?q='+key_words+'&p='+(page_id+1)+"&s_type="+$(".searchItem.current").attr('data-type')
    }

    setHeight();
    $(window).resize(function(){
        setHeight();
    });

    function setHeight(){
        if($('#container').outerHeight() < $(window).height()){
            $('#container').height($(window).height()-33);
        }
    }
</script>
<script type="text/javascript">
    $('.searchList').on('click', '.searchItem', function(){
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    // 联想下拉显示隐藏
    $('.searchInput').on('focus', function(){
        $('.dataList').show()
    });

    // 联想下拉点击
    $('.dataList').on('click', 'li', function(){
        var text = $(this).text();
        $('.searchInput').val(text);
        $('.dataList').hide()
    });

    hideElement($('.dataList'), $('.searchInput'));
</script>
<script>
    var searchArr;
    //定义一个search的，判断浏览器有无数据存储（搜索历史）
    if(localStorage.search){
        //如果有，转换成 数组的形式存放到searchArr的数组里（localStorage以字符串的形式存储，所以要把它转换成数组的形式）
        searchArr= localStorage.search.split(",")
    }else{
        //如果没有，则定义searchArr为一个空的数组
        searchArr = [];
    }
    //把存储的数据显示出来作为搜索历史
    MapSearchArr();

    function add_search(){
        var val = $(".searchInput").val();
        if (val.length>=2){
            //点击搜索按钮时，去重
            KillRepeat(val);
            //去重后把数组存储到浏览器localStorage
            localStorage.search = searchArr;
            //然后再把搜索内容显示出来
            MapSearchArr();
        }

        window.location.href=search_url+'?q='+val+"&s_type="+$(".searchItem.current").attr('data-type')

    }

    function MapSearchArr(){
        var tmpHtml = "";
        var arrLen = 0
        if (searchArr.length > 6){
            arrLen = 6
        }else {
            arrLen = searchArr.length
        }
        for (var i=0;i<arrLen;i++){
            tmpHtml += '<li><a href="/search?q='+searchArr[i]+'&s_type='+$(".searchItem.current").attr('data-type')+'">'+searchArr[i]+'</a></li>'
        }
        $(".mySearch .historyList").append(tmpHtml);
    }
    //去重
    function KillRepeat(val){
        var kill = 0;
        for (var i=0;i<searchArr.length;i++){
            if(val===searchArr[i]){
                kill ++;
            }
        }
        if(kill<1){
            searchArr.unshift(val);
        }else {
            removeByValue(searchArr, val)
            searchArr.unshift(val)
        }
    }
</script>
</html>
