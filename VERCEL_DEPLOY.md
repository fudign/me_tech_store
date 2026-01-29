# 5?;>9 =0 Vercel - >;=0O 8=AB@C:F8O

## 1. >43>B>2:0 : 45?;>N

### "@51>20=8O
- ::0C=B =0 Vercel (A2O70==K9 A GitHub)
- 070 40==KE PostgreSQL (@5:><5=4C5BAO Supabase)
- APP_KEY 4;O Laravel

## 2. 5=5@0F8O APP_KEY

K?>;=8B5 ;>:0;L=>:
```bash
php artisan key:generate --show
```

!:>?8@C9B5 ?>;CG5==K9 :;NG (D>@<0B: `base64:...`)

## 3. !>740=85 ?@>5:B0 =0 Vercel

1. 5@5948B5 =0 [vercel.com](https://vercel.com)
2. >948B5 G5@57 GitHub
3. 06<8B5 "Add New Project"
4. K15@8B5 @5?>78B>@89 `me_tech_store`

### 0AB@>9:8 ?@>5:B0:

- **Framework Preset**: Other
- **Build Command**: `npm run vercel-build`
- **Output Directory**: (>AB02LB5 ?CABK<)
- **Install Command**: `npm install`

## 4. 0AB@>9:0 ?5@5<5==KE >:@C65=8O

5@5948B5 2 Settings ’ Environment Variables 8 4>102LB5:

### 1O70B5;L=K5 ?5@5<5==K5:

```env
APP_NAME=Mi Tech Store
APP_ENV=production
APP_KEY=base64:(_.'__(_2
APP_DEBUG=false
APP_URL=https://20H-4><5=.vercel.app
APP_TIMEZONE=Asia/Bishkek
APP_LOCALE=ru
```

### 070 40==KE (PostgreSQL/Supabase):

```env
DB_CONNECTION=pgsql
DB_HOST=db.wtevayfmmvrbtevxsbwh.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=(_ ,_SUPABASE
```

### Supabase (5A;8 8A?>;L7C5BAO):

```env
SUPABASE_URL=https://wtevayfmmvrbtevxsbwh.supabase.co
SUPABASE_KEY=20H_supabase_anon_key
SUPABASE_SERVICE_KEY=20H_supabase_service_key
```

### >=D83C@0F8O 4;O Vercel:

```env
SESSION_DRIVER=cookie
CACHE_STORE=array
LOG_CHANNEL=stderr
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

## 5. 5?;>9

06<8B5 :=>?:C "Deploy" 8 4>648B5AL 7025@H5=8O A1>@:8.

## 6. 803=>AB8:0 ?@>1;5<

### A;8 ?>;CG8;8 >H81:C 500:

1. **@>25@LB5 ?>4@>1=CN >H81:C:**
   - B:@>9B5 20H A09B 2 1@0C75@5
   - K C2848B5 45B0;L=>5 >?8A0=85 >H81:8
   - @>25@LB5 A>>1I5=85 >1 >H81:5

2. **803=>AB8G5A:0O AB@0=8F0:**
   >102LB5 : URL ?0@0<5B@ `?_vercel_debug=true`
   ```
   https://20H-4><5=.vercel.app?_vercel_debug=true
   ```
   -B> ?>:065B:
   - 5@A8N PHP
   - #AB0=>2;5==K5 ?5@5<5==K5 >:@C65=8O
   - 0;8G85 =5>1E>48<KE D09;>2
   - @020 4>ABC?0 : 48@5:B>@8O<

3. **@>25@LB5 ;>38 Vercel:**
   - 5@5948B5 2 Deployments
   - K15@8B5 ?>A;54=89 45?;>9
   - B:@>9B5 2:;04:C "Logs"
   - 0948B5 AB@>:8 A "VERCEL ERROR"

### '0ABK5 ?@>1;5<K:

#### L "No application encryption key has been specified"
** 5H5=85:** APP_KEY =5 CAB0=>2;5= 8;8 =525@=K9 D>@<0B
- !35=5@8@C9B5 =>2K9 :;NG: `php artisan key:generate --show`
- #1548B5AL, GB> D>@<0B: `base64:xxxxx`

#### L "SQLSTATE[08006] Connection refused"
** 5H5=85:** @>1;5<K A 107>9 40==KE
- @>25@LB5 DB_HOST, DB_USERNAME, DB_PASSWORD
- #1548B5AL, GB> 1070 40==KE 4>ABC?=0 872=5
- @>25@LB5, GB> DB_PORT=5432

#### L "Class 'X' not found"
** 5H5=85:** @>1;5<K A composer 7028A8<>ABO<8
- @>25@LB5 Build Logs 2 Vercel
- #1548B5AL, GB> `composer install` 2K?>;=8;AO CA?5H=>

#### L "View not found" 8;8 ?@>1;5<K A ?CBO<8
** 5H5=85:** @>1;5<K A :MH5< 8;8 ?CBO<8
- G8AB8B5 :MH: Redeploy ?@>5:B0
- @>25@LB5, GB> VIEW_COMPILED_PATH =0AB@>5=

## 7. >A;5 CA?5H=>3> 45?;>O

1. 1=>28B5 APP_URL =0 @50;L=K9 4><5= Vercel
2. #AB0=>28B5 APP_DEBUG=false 4;O ?@>40:H5=0
3. 0AB@>9B5 custom domain (>?F8>=0;L=>)
4. @>25@LB5 @01>BC:
   - ;02=0O AB@0=8F0
   - 0B0;>3 B>20@>2
   - 4<8=-?0=5;L `/admin/login`

## 8. 2B><0B8G5A:85 >1=>2;5=8O

>A;5 CA?5H=>3> 45?;>O:
- 064K9 `git push` 2 25B:C `main` 02B><0B8G5A:8 7045?;>8BAO
- Preview deployments A>740NBAO 4;O pull requests
- Rollback 4>ABC?5= 2 ;N1>9 <><5=B G5@57 Vercel Dashboard

## 9. 06=K5 >3@0=8G5=8O Vercel

1. **$09;>20O A8AB5<0 read-only**
   - 03@C65==K5 D09;K =5 A>E@0=ONBAO
   - A?>;L7C9B5 Supabase Storage 8;8 S3 4;O D09;>2

2. **57 D>=>2>3> 2K?>;=5=8O**
   - Queues @01>B0NB B>;L:> sync
   - Scheduled tasks =5 ?>445@6820NBAO

3. **8<8BK 2@5<5=8 2K?>;=5=8O**
   - 10 A5:C=4 4;O Hobby ?;0=
   - 60 A5:C=4 4;O Pro ?;0=

## 10. >445@6:0

A;8 ?@>1;5<0 =5 @5H05BAO:
1. B:@>9B5 `?_vercel_debug=true`
2. @>25@LB5 Vercel Logs
3. @>25@LB5 45B0;L=CN >H81:C =0 AB@0=8F5
4. #1548B5AL, GB> 2A5 ?5@5<5==K5 >:@C65=8O CAB0=>2;5=K
