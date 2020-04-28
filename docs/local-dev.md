# 本地开发

使用 docker 搭建本地环境：

```yaml
version: '3'

services:
  mysql:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: "pa3sW0rd"
    volumes:
      - ./mysql-data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin" ,"ping", "-h", "localhost"]
      timeout: 20s
      retries: 10
  tars:
    image: wenbinye/tars
    ports:
      - '3000:3000'
      - '3001:3001'
      - 12000:12000
      - 17890:17890
      - 17891:17891
      - 18193:18193
      - 18293:18293
      - 18393:18393
      - 18493:18493
      - 18593:18593
      - 18693:18693
      - 18793:18793
      - 18993:18993
      - 19385:19385      
    environment:
      MYSQL_HOST: 'mysql'
      MYSQL_ROOT_PASSWORD: 'pa3sW0rd'
      REBUILD: 'true'
    links:
      - mysql
    depends_on:
      - mysql
    volumes:
      - ./tars-data:/data/tars
```
```bash
docker-compose up -d
```

启动后打开 http://localhost:3000 。

> 正常是会提示设置 admin 密码，如果没有，使用如下命令设置
> ``` 
> docker exec -it tars_mysql_1 mysql -uroot -ppa3sW0rd db_user_system -e "update t_user_info set password = '7c4a8d09ca3762af61e59520943dc26494f8941b'"
> ```
> 密码可以使用 `php -r 'echo sha1("123456");'` 生成。