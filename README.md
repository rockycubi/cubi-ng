Cubi-Ng
=======

Cubi-Ng provides a practical framework of creating Single Page Application (SPA) based on angularjs.

Some history
------------

Cubi-Ng is a combination of Cubi and Angularjs. Also you may call it NextGeneration Cubi. 

Cubi was initially an open source application platform https://code.google.com/p/openbiz-cubi/. It was written on top of openbiz library which is a framework with metadata driven development architecture (MDDA). Cubi provides a set of common components and tools for building a complicated web application. Developing web app on Cubi is easy and efficient.

Angularjs https://angularjs.org/ a fantastic javascript library featured with two-way data binding. It is widely used since 2013 to create modern single page applications. It gets more popular comparing with other javascript frameworks. See trend at http://www.google.com/trends/explore?hl=en-US#q=Angularjs,+Backbone.js,+Ember.js&date=today+12-m&cmpt=q

Current Web Technology Trend
============================
As the following chart demonstrated, web architecture has evolved through 3 stages:
- Model 1: classic web application
- Model 2: AJAX web application
- Model 3: Client side MV web application

![web tech trend](http://blog.octo.com/wp-content/uploads/2014/03/web-application-models-over-time.png)

Cubi is a mature web platform based on Model 2 (AJAX web application), Cubi-Ng is the Model 3 new generation. Its goal is to make single page application development easier.

With Model 3, SPA is a typical UI theme.

Single Page Application
-----------------------
What is SPA? A single-page application (SPA), also known as single-page interface (SPI), is a web application or web site that fits on a single web page with the goal of providing a more fluid user experience akin to a desktop application. This definition is copied from Wikipedia http://en.wikipedia.org/wiki/Single-page_application.

Single page applications (SPA) are more capable of decreasing load time of pages by storing the functionality once it is loaded the first time, allowing easier data transfer between pages and a more complex user interface instead of trying to control so much from the server. This allows for more interference from an end user. 

Cubi-Ng Programming Model
=========================
Cubi-Ng is built on top of metadata driven development architecture (MDDA). It means programming in Cubi-Ng is editing XML metadata most time. Let's use a table UI to demonstrate the concept.

To implement a listing UI as below

![User listing UI](https://raw.githubusercontent.com/rockycubi/cubi-docs/master/images/user_list.png)

In Cubi-Ng, you can simply create a XML file and the framework will render the UI on browser. As angularjs is built-in, user  interaction experience on the listing is fast and smooth.
```xml
<EasyForm Name="UserListForm" Icon="icon_user_list.gif" Class="UserForm" Title="User Management" Description="Manage user accounts in the application" BizDataObj="system.do.UserDO" DataService="/system/users" TemplateEngine="Smarty" TemplateFile="system_right_listform.tpl.html"  Access="User.Administer_Users">
    <DataPanel>
        <Element Name="fld_Id" Class="ColumnText" FieldName="Id" Label="ID" Sortable="Y"/>
        <Element Name="fld_username" Class="ColumnText" FieldName="username" Label="Username" Link="{@home:url}/system/user_detail/{{dataobj.Id}}" Sortable="Y"/>
        <Element Name="fld_email" Class="ColumnText" FieldName="email" Label="Email" Sortable="Y"/>
		<Element Name="fld_status" Class="ColumnBool" FieldName="status" Label="Active" Sortable="Y" />
		<Element Name="fld_lastlogin" Class="ColumnText" FieldName="lastlogin" Label="Last Login" Sortable="Y" />
    </DataPanel>
    <ActionPanel>
        <Element Name="lnk_new" Class="Button" Text="Add" CssClass="button_gray_add" Description="new record (Insert)" Link="{@home:url}/system/user_new"/>
        <Element Name="btn_edit" Class="Button" Text="Edit" CssClass="button_gray_m" Link="{@home:url}/system/user_edit/{{selectedId}}"/>
        <Element Name="btn_delete" Class="Button" Text="Delete" CssClass="button_gray_m" Click="delete(selectedIndex)"/>    		
    </ActionPanel> 
    <NavPanel>
        <Element Name="btn_first" Class="Button" CssClass="button_gray_navi first" Click="gotoPage(1)"/>
        <Element Name="btn_prev" Class="Button" CssClass="button_gray_navi prev" Click="gotoPage(currentPage-1)"/>
        <Element Name="txt_page" Class="LabelText" Text="{{currentPage}} of {{totalPage}}"/>
        <Element Name="btn_next" Class="Button" CssClass="button_gray_navi next" Click="gotoPage(currentPage+1)"/>
        <Element Name="btn_last" Class="Button" CssClass="button_gray_navi last" Click="gotoPage(totalPage)"/>
    </NavPanel> 
    <SearchPanel>
        <Element Name="qry_username" Class="InputText" FieldName="username" CssClass="input_text_search"/>
        <Element Name="btn_dosearch" Class="Button" text="Go" CssClass="button_gray" Click="search()"/>       
    </SearchPanel>
</EasyForm>
```

The Benefits of Metadata Driven Development
-------------------------------------------
Metadata driven development (MDD) means developers build business applications using metadata and not using code. Metadata is a high level definition of business objects like data model, services, forms, dialogs, validations and other things that are part of a business process.

Comparing metadata driven development with traditional coding or manual programming, unlike code which is tied to a specific platform or operating system, metadata is a high level definition and theoretically can run on any platform or operating system.

More reading on MDD, [Metadata driven development, the Holy Grail of software development](http://janvanderhaegen.com/2011/12/19/metadata-driven-development-the-holy-grail-of-software-development/)

Get start
=========
- [Installation](https://github.com/rockycubi/cubi-ng/wiki/CubiNG-Installation)
- [Overview](https://github.com/rockycubi/cubi-ng/wiki/CubiNG-Overview)
- [UI Architecture](https://github.com/rockycubi/cubi-ng/wiki/CubiNG-UI-Architecture)
- [Web Services](https://github.com/rockycubi/cubi-ng/wiki/CubiNG-Web-Services)

Demo
----
The cubi-ng demo is hosted at OpenShift cloud platform. Visit http://openbiz-cubing.rhcloud.com/index.php/user/login, login with username:demo1, password: demo123. Once you login the application, your landing page is "My Account". You can click "Administration" top link to play with core cubi-ng modules user, role, group, menu and module.
- Cubi-ng system modules, https://code.google.com/p/openbiz-cubi/wiki/CubiSystemModule
- Cubi-ng menu module, https://code.google.com/p/openbiz-cubi/wiki/CubiMenuModule
- Cubi-ng myaccount module, https://code.google.com/p/openbiz-cubi/wiki/CubiMyAccount. 
- Cubi-ng user module, https://code.google.com/p/openbiz-cubi/wiki/CubiUserModule. Please be aware that the "Register new account" and "Forget password" links are not functional yet.

Get help
--------
- If you have any question, you can email me rocky@openbiz.me
- For bugs, please create issues at https://github.com/rockycubi/cubi-ng/issues
