#Run daily to download latest NASA Fire Data - then check against existing monitorzones (polygons) for all Active Projects

import os
from sqlalchemy import create_engine
import pandas as pd
from pandas.io import sql
from datetime import datetime
from pathlib import Path
import shutil
import fiona
import psycopg2
from postmarker.core import PostmarkClient

###########################################################################
#  Check Values
###########################################################################

postmark_token = os.environ.get('POSTMARK_TOKEN')

if postmark_token is None:
  print("Token not found so sending via POSTMARK_API_TEST black hole - good if no errors")
  postmark_token = 'POSTMARK_API_TEST'
postmark = PostmarkClient(server_token=postmark_token)


password = os.environ.get('POSTGRES_PASSWORD')

if password is None:
    print ('No postgres password found, defaulting to test which is used in dev')
    password = 'test'

username = 'postgres'

today = datetime.today().strftime('%Y_%m_%d')
downloadpath= "/var/datadownloads/"+str(today)+"/"

###########################################################################

engine = create_engine('postgresql://postgres:'+password+'@localhost:5432/nasafiremap')
dirpath = Path(downloadpath)


#---------------------------------------------------------------------------
#create DIR for data download for the day
def setupDir():
    if dirpath.exists() and dirpath.is_dir():
        shutil.rmtree(dirpath)

    try:
        os.mkdir(downloadpath)
    except OSError:
        return False
    else:
        return True



#---------------------------------------------------------------------------
#get the NASA fire map data
def getData():
    print ('================> Downloading NASA Fire data: DAILY 24h')
    fn1 = 'https://firms.modaps.eosdis.nasa.gov/data/active_fire/modis-c6.1/shapes/zips/MODIS_C6_1_Global_24h.zip'
    fn2= 'https://firms.modaps.eosdis.nasa.gov/data/active_fire/suomi-npp-viirs-c2/shapes/zips/SUOMI_VIIRS_C2_Global_24h.zip'
    fn3 = 'https://firms.modaps.eosdis.nasa.gov/data/active_fire/noaa-20-viirs-c2/shapes/zips/J1_VIIRS_C2_Global_24h.zip'

         #CSV version https://firms.modaps.eosdis.nasa.gov/data/active_fire/modis-c6.1/csv/MODIS_C6_1_Global_24h.csv


    os.system ("wget -c --read-timeout=8 --tries=0 "+ fn1  + " -O " + downloadpath+'MODIS_C6_1_Global_24h.zip')
    os.system("wget -c --read-timeout=8 --tries=0 " + fn2 + " -O " + downloadpath+'SUOMI_VIIRS_C2_Global_24h.zip' )
    os.system("wget -c --read-timeout=8 --tries=0 " +fn3 + " -O " + downloadpath+'J1_VIIRS_C2_Global_24h.zip' )

#---------------------------------------------------------------------------
#unzip files
def unZIP():

    print ('================> unzipping DAILY 24h datasets')

    unzip1 = "MODIS_C6_1_Global_24h.zip"
    unzip2 =  "SUOMI_VIIRS_C2_Global_24h.zip"
    unzip3 = "J1_VIIRS_C2_Global_24h.zip"

    f1 = downloadpath +unzip1 

    print (f1)
    print ("unzip " + downloadpath +unzip1 )

    os.system("unzip " + downloadpath +unzip1 + " -d "+ downloadpath )
    os.system("unzip "+ downloadpath +unzip2 + " -d "+ downloadpath)
    os.system("unzip "+ downloadpath +unzip3 + " -d "+ downloadpath)

#---------------------------------------------------------------------------
#upload to PostgreSQL
def uploadtoPostgreSQL():

    print ('================> Uploading DAILY 24h datasets to PostgreSQL')
    
    sql.execute('DROP TABLE IF EXISTS MODIS_C6_1_Global_24h'  , engine)
    sql.execute('DROP TABLE IF EXISTS J1_VIIRS_C2_Global_24h'  , engine)
    sql.execute('DROP TABLE IF EXISTS SUOMI_VIIRS_C2_Global_24h'  , engine)

    #alt method to load with ogr2ogr
    #cmd1 = 'ogr2ogr -f "PostgreSQL" PG:"dbname=nasafiremap user='+username+' password='+password+' " '+downloadpath + 'SUOMI_VIIRS_C2_Global_24h.shp -nln daily_suomi -skip-failures  -overwrite '
    #cmd2 = 'ogr2ogr -f "PostgreSQL" PG:"dbname=nasafiremap user='+username+' password='+password+' " '+downloadpath + 'MODIS_C6_1_Global_24h.shp  -nln daily_modis -skip-failures  -overwrite'
    #cmd3 = 'ogr2ogr -f "PostgreSQL" PG:"dbname=nasafiremap user='+username+' password='+password+' " '+downloadpath + 'J1_VIIRS_C2_Global_24h.shp  -nln daily_viirs  -skip-failures  -overwrite'
    
    cmd1 =  "shp2pgsql -s 4326 "+ downloadpath + "MODIS_C6_1_Global_24h.shp | PGPASSWORD="+password+" psql -h 127.0.0.1 -d nasafiremap -U "+ username +" -q "
    cmd2 =  "shp2pgsql -s 4326 "+ downloadpath + "J1_VIIRS_C2_Global_24h.shp | PGPASSWORD="+password+" psql -h 127.0.0.1 -d nasafiremap -U "+ username +" -q "
    cmd3 =  "shp2pgsql -s 4326 "+ downloadpath + "SUOMI_VIIRS_C2_Global_24h.shp | PGPASSWORD="+password+" psql -h 127.0.0.1 -d nasafiremap -U "+username +" -q "
    
    os.system(cmd1)
    os.system(cmd2)
    os.system(cmd3)

#---------------------------------------------------------------------------
#run checks (dates, number of records)
def runChecks():

    recordsloadedOK=True

    print ('------------Running checks on DAILY 24h datasets---------')

###### MODIS 24h
    print ("=====>>>>"+downloadpath+'MODIS_C6_1_Global_24h.shp')

    with fiona.open(downloadpath+'MODIS_C6_1_Global_24h.shp') as input:
        records=len(input)

    dfcheck = pd.read_sql("select count(*) from "+ 'MODIS_C6_1_Global_24h'.lower(),con=engine)
    print (dfcheck.iloc[0]['count'])

    if (abs(dfcheck.iloc[0]['count'] - records) >1): #allow 1 record diff 
        print ('record loading issue with MODIS_C6_1_Global_24h')
        recordsloadedOK=False


###### SUOMI VIIRS 24h 
    with fiona.open(downloadpath+'SUOMI_VIIRS_C2_Global_24h.shp') as input:
        records=len(input)

    dfcheck = pd.read_sql("select count(*) from "+ 'SUOMI_VIIRS_C2_Global_24h'.lower(), con=engine)
    print (dfcheck.iloc[0]['count'])

    if (abs( dfcheck.iloc[0]['count'] - records) >1 ): #allow 1 record diff
        print ('record loading issue with SUOMI_VIIRS_C2_Global_24h')
        recordsloadedOK=False


###### J1 VIIRS 24h 
    with fiona.open(downloadpath+'J1_VIIRS_C2_Global_24h.shp') as input:
        records=len(input)

    dfcheck = pd.read_sql("select count(*) from "+ 'J1_VIIRS_C2_Global_24h'.lower(), con=engine)
    print (dfcheck.iloc[0]['count'])

    if (abs (dfcheck.iloc[0]['count'] - records) >1):  #allow 1 record diff
        print ('record loading issue with J1_VIIRS_C2_Global_24h')
        recordsloadedOK=False


    return recordsloadedOK


#---------------------------------------------------------------------------
def notifyadmin(err):
    #Send msg to admin team about daily load error

    print ('Daily Reporting:',err)
        
    postmark.emails.send(
        From='help@osr4rightstools.org',
        To='davemateer@gmail.com',
        Subject='Fire-Map',
        HtmlBody='Message is: ' + err
    )


#---------------------------------------------------------------------------
def setSRID():
    print ('setting SRID to 4326')
    #check ACTIVE polygons against the tables and create report
    sql = "UPDATE modis_c6_1_global_24h set geom = st_setsrid (geom,4326); " 
    sql += "UPDATE j1_viirs_c2_global_24h set geom = st_setsrid (geom,4326);"
    sql += "UPDATE suomi_viirs_c2_global_24h set geom = st_setsrid (geom,4326);"


    conn = psycopg2.connect(
        database="nasafiremap", user=username, password=password, host='localhost', port='5432')
    
    conn.autocommit = True
    cursor = conn.cursor()
    
    cursor.execute(sql)
    
    conn.commit()
    conn.close()


#---------------------------------------------------------------------------
def buildindexes(stage):

    if (stage==1):
        print ('building indexes on date and geom')
        #check ACTIVE polygons against the tables and create report
        sql = "CREATE INDEX on modis_c6_1_global_24h using gist(geom); " 
        sql += "CREATE INDEX on j1_viirs_c2_global_24h using gist(geom); " 
        sql += "CREATE INDEX on suomi_viirs_c2_global_24h using gist(geom); " 

        sql += "CREATE INDEX on modis_c6_1_global_24h using btree(acq_date); " 
        sql += "CREATE INDEX on j1_viirs_c2_global_24h using btree(acq_date); " 
        sql += "CREATE INDEX on suomi_viirs_c2_global_24h using btree(acq_date); "
    elif (stage==2):
        sql = "CREATE INDEX on dailyreport using gist(geom); " 
        sql += "CREATE INDEX on dailyreport using btree(acq_date); " 
        sql += "CREATE INDEX on dailyreport using btree(projectid); " 
        sql += "CREATE INDEX on dailyreport using btree(polyid); " 



    conn = psycopg2.connect(
        database="nasafiremap", user=username, password=password, host='localhost', port='5432')
    
    conn.autocommit = True
    cursor = conn.cursor()
    
    cursor.execute(sql)
    
    conn.commit()
    conn.close()
    print ('index creation finished')


#---------------------------------------------------------------------------
def runreport():
    print ('Run Report - looking at new data from nasa and checking with monitorzones table')
    #check ACTIVE polygons against the tables and create report
    sql = """
-- create a temp table with indexes is faster than sub query (2 seconds vs 40 minutes)
DROP TABLE IF EXISTS all_years;
CREATE TEMP TABLE all_years as
 			SELECT 'modis' as source, acq_date,acq_time,confidence::text,daynight,geom FROM modis_c6_1_global_24h
            UNION
            SELECT 'j1_viirs' as source, acq_date,acq_time,confidence::text,daynight, geom FROM j1_viirs_c2_global_24h
            UNION
            SELECT 'sumoi_viirs' as source, acq_date,acq_time,confidence::text,daynight, geom FROM suomi_viirs_c2_global_24h;

CREATE INDEX on all_years using gist(geom);
CREATE INDEX on all_years using btree(acq_date); 

DROP TABLE IF EXISTS dailyreport;
CREATE TABLE dailyreport as 
            SELECT fd.*, p.projectid,p.notification_emailaddress,m.polyid  FROM all_years fd
            JOIN
            monitorzones m on ST_DWITHIN (fd.geom,m.geom,0.01)
            JOIN project p on m.projectid=p.projectid
            where p.active = True;
    """

    conn = psycopg2.connect(
        database="nasafiremap", user=username, password=password, host='localhost', port='5432')
    
    conn.autocommit = True
    cursor = conn.cursor()
    
    cursor.execute(sql)
    
    conn.commit()
    conn.close()
    print ('Run Report done')


#---------------------------------------------------------------------------
def runarchive():
    #make a copy of daily reports but PK restricts adding duplicates based on geom, date,time, polyid
    sql = """
           INSERT INTO public.dailyreporthistory select * from dailyreport on conflict do nothing;          
        """

    conn = psycopg2.connect(
        database="nasafiremap", user=username, password=password, host='localhost', port='5432')
    
    conn.autocommit = True
    cursor1 = conn.cursor()
    cursor1.execute(sql)
    conn.commit()

    cursor1.close()


    sql2 = """
    INSERT INTO public.dailyreport_polyhistory 
    select dailyreport.polyid,now(), monitorzones.geom 
    from dailyreport join monitorzones on dailyreport.polyid=monitorzones.polyid
    on conflict do nothing;"""

    conn.autocommit = True
    cursor2 = conn.cursor()
    cursor2.execute(sql2)
    conn.commit()

    cursor2.close()


    conn.close()








#=============================  START OF MAIN PROCESS  =============================

createDir = setupDir()

if (createDir):

    allloaded = False;

    getData()

    unZIP()

    uploadtoPostgreSQL()

    #check shp records against DB table counts
    allloaded=runChecks()

    print (allloaded)

    if (allloaded):
        #set the SRIDs and then build indexes on geom and date columns
        setSRID()
        buildindexes(1) ##first time build geom and date indexes
        
        #run reports
        runreport()
        buildindexes(2)  #2nd time index daily results

        #archive daily data to a dailyreporthistory table
        runarchive()

        notifyadmin('Data loaded OK')


    else:
        print ("Issue in Loading Daily Fire Records")
        notifyadmin('Failed to load all data to DB')
else:
    print ("Issue creating directory")

#---------------------------------------------------------------------------


