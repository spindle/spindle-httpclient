/**
 * sample API
 */

var http = require('http');

var server = http.createServer(function(req, res){

    //routing
    switch (req.url) {
        case '/simple':
            res.writeHead(200, {
                'Content-Type': 'text/plain'
            });
            res.end('simple');
            break;

        case '/simple.xml':
            res.writeHead(200, {
                'Content-Type': 'application/xml'
            });
            res.end('<root>simple</root>');
            break;

        case '/slow1':
            setTimeout(function(){
                res.writeHead(200, {
                    'Content-Type': 'text/plain'
                });
                res.end('slow1');
            }, 1000);
            break;
    }
});

server.listen(1337, 'localhost');
