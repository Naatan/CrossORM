Lightweight ORM based on Idiorm and Paris

Project goals

* Intuitive ORM, you shouldn't need to check a doc every couple of minutes
* Support databases through drivers, each db driver can choose not to implement certain features or to implement features others dont have. (point being that you have your core ORM to start from every time you begin a new project)
* Don't make use of needles starter methods or "get" methods when querying on a model (unless that's what you want).
* Intuitive active record methods. When a model is aware of the fields in a table, why not just use where\_name('jack') instead of where('name','jack').
* Build in validation
* Support for relations on non-relational database (through multiple queries)
* ACL on a per table, per field basis

Probably more to come as I go along, I'm making this for myself to use and sharing it with the open source community, so for the time being I'm not taking the project all too serious in terms of planning & testing.