
# -----------------------------------------------------------------------------
#       TABLE : UTILISATEUR
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS UTILISATEUR
 (
   IDUSER BIGINT(4) NOT NULL AUTO_INCREMENT ,
   USERNAME CHAR(32) NULL  ,
   NAME CHAR(32) NULL  ,
   SIGNUP_DATE DATE NULL  ,
   EMAIL CHAR(32) NULL  ,
   PASS CHAR(32) NULL  
   , PRIMARY KEY (IDUSER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : TWEET
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS TWEET
 (
   IDTWEET BIGINT(4) NOT NULL AUTO_INCREMENT ,
   IDUSER BIGINT(4) NOT NULL  ,
   IDTWEET_REPONDRE BIGINT(4) NULL  ,
   DATE_P DATE NULL  ,
   TEXTE CHAR(255) NULL  ,
   USERNAME CHAR(32) NULL  
   , PRIMARY KEY (IDTWEET) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE TWEET
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_TWEET_UTILISATEUR
     ON TWEET (IDUSER ASC);

CREATE  INDEX I_FK_TWEET_TWEET
     ON TWEET (IDTWEET_REPONDRE ASC);

# -----------------------------------------------------------------------------
#       TABLE : HASHTAG
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS HASHTAG
 (
   TAG CHAR(50) NOT NULL  ,
   DATE_P DATE NULL  
   , PRIMARY KEY (TAG) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : SUIVRE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS SUIVRE
 (
   IDUSER BIGINT(4) NOT NULL  ,
   IDUSER_1 BIGINT(4) NOT NULL  ,
   DATE_SUIVI DATE NULL  
   , PRIMARY KEY (IDUSER,IDUSER_1) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE SUIVRE
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_SUIVRE_UTILISATEUR
     ON SUIVRE (IDUSER ASC);

CREATE  INDEX I_FK_SUIVRE_UTILISATEUR1
     ON SUIVRE (IDUSER_1 ASC);

# -----------------------------------------------------------------------------
#       TABLE : MENTIONNER
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS MENTIONNER
 (
   IDTWEET BIGINT(4) NOT NULL  ,
   IDUSER BIGINT(4) NOT NULL  ,
   DATE_MENTION DATE NULL  
   , PRIMARY KEY (IDTWEET,IDUSER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE MENTIONNER
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_MENTIONNER_TWEET
     ON MENTIONNER (IDTWEET ASC);

CREATE  INDEX I_FK_MENTIONNER_UTILISATEUR
     ON MENTIONNER (IDUSER ASC);

# -----------------------------------------------------------------------------
#       TABLE : REFERENCE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS REFERENCE
 (
   TAG CHAR(50) NOT NULL  ,
   IDTWEET BIGINT(4) NOT NULL  
   , PRIMARY KEY (TAG,IDTWEET) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE REFERENCE
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_REFERENCE_HASHTAG
     ON REFERENCE (TAG ASC);

CREATE  INDEX I_FK_REFERENCE_TWEET
     ON REFERENCE (IDTWEET ASC);

# -----------------------------------------------------------------------------
#       TABLE : AIMER
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS AIMER
 (
   IDTWEET BIGINT(4) NOT NULL  ,
   IDUSER BIGINT(4) NOT NULL  ,
   DATE_LIKE DATE NULL  
   , PRIMARY KEY (IDTWEET,IDUSER) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE AIMER
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_AIMER_TWEET
     ON AIMER (IDTWEET ASC);

CREATE  INDEX I_FK_AIMER_UTILISATEUR
     ON AIMER (IDUSER ASC);


# -----------------------------------------------------------------------------
#       CREATION DES REFERENCES DE TABLE
# -----------------------------------------------------------------------------


ALTER TABLE TWEET 
  ADD FOREIGN KEY FK_TWEET_UTILISATEUR (IDUSER)
      REFERENCES UTILISATEUR (IDUSER) ;


ALTER TABLE TWEET 
  ADD FOREIGN KEY FK_TWEET_TWEET (IDTWEET_REPONDRE)
      REFERENCES TWEET (IDTWEET) ;


ALTER TABLE SUIVRE 
  ADD FOREIGN KEY FK_SUIVRE_UTILISATEUR (IDUSER)
      REFERENCES UTILISATEUR (IDUSER) ;


ALTER TABLE SUIVRE 
  ADD FOREIGN KEY FK_SUIVRE_UTILISATEUR1 (IDUSER_1)
      REFERENCES UTILISATEUR (IDUSER) ;


ALTER TABLE MENTIONNER 
  ADD FOREIGN KEY FK_MENTIONNER_TWEET (IDTWEET)
      REFERENCES TWEET (IDTWEET) ;


ALTER TABLE MENTIONNER 
  ADD FOREIGN KEY FK_MENTIONNER_UTILISATEUR (IDUSER)
      REFERENCES UTILISATEUR (IDUSER) ;


ALTER TABLE REFERENCE 
  ADD FOREIGN KEY FK_REFERENCE_HASHTAG (TAG)
      REFERENCES HASHTAG (TAG) ;


ALTER TABLE REFERENCE 
  ADD FOREIGN KEY FK_REFERENCE_TWEET (IDTWEET)
      REFERENCES TWEET (IDTWEET) ;


ALTER TABLE AIMER 
  ADD FOREIGN KEY FK_AIMER_TWEET (IDTWEET)
      REFERENCES TWEET (IDTWEET) ;


ALTER TABLE AIMER 
  ADD FOREIGN KEY FK_AIMER_UTILISATEUR (IDUSER)
      REFERENCES UTILISATEUR (IDUSER) ;

