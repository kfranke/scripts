FasdUAS 1.101.10   ��   ��    k             l     ��  ��      Description     � 	 	    D e s c r i p t i o n   
  
 l     ��  ��    I C This doesnt currently work. It's all dependant on keystroke timing     �   �   T h i s   d o e s n t   c u r r e n t l y   w o r k .   I t ' s   a l l   d e p e n d a n t   o n   k e y s t r o k e   t i m i n g      l     ��  ��    G A which varies on various things like, computer load, avail memory     �   �   w h i c h   v a r i e s   o n   v a r i o u s   t h i n g s   l i k e ,   c o m p u t e r   l o a d ,   a v a i l   m e m o r y      l     ��  ��      proc speed, etc.      �   $   p r o c   s p e e d ,   e t c .        l     ��  ��           �           l     ��   !��     ; 5 This script exports the tasks from the Reminders app    ! � " " j   T h i s   s c r i p t   e x p o r t s   t h e   t a s k s   f r o m   t h e   R e m i n d e r s   a p p   # $ # l     �� % &��   % < 6 as an ics file. After exporting it calls a php script    & � ' ' l   a s   a n   i c s   f i l e .   A f t e r   e x p o r t i n g   i t   c a l l s   a   p h p   s c r i p t $  ( ) ( l     �� * +��   * < 6 which processes the ics file, pulls out relevent info    + � , , l   w h i c h   p r o c e s s e s   t h e   i c s   f i l e ,   p u l l s   o u t   r e l e v e n t   i n f o )  - . - l     �� / 0��   / < 6 and creates a MS Excel file with it. Lastly it opens     0 � 1 1 l   a n d   c r e a t e s   a   M S   E x c e l   f i l e   w i t h   i t .   L a s t l y   i t   o p e n s   .  2 3 2 l     �� 4 5��   4 3 - up a new email and attached the spreadsheet.    5 � 6 6 Z   u p   a   n e w   e m a i l   a n d   a t t a c h e d   t h e   s p r e a d s h e e t . 3  7 8 7 l     �� 9 :��   9 : 4 Intended usage is to auto email the stuff worked on    : � ; ; h   I n t e n d e d   u s a g e   i s   t o   a u t o   e m a i l   t h e   s t u f f   w o r k e d   o n 8  < = < l     �� > ?��   > 0 * to a manager requiring frequent updates.     ? � @ @ T   t o   a   m a n a g e r   r e q u i r i n g   f r e q u e n t   u p d a t e s .   =  A B A l     ��������  ��  ��   B  C D C l     �� E F��   E #  Declare the global variables    F � G G :   D e c l a r e   t h e   g l o b a l   v a r i a b l e s D  H I H p       J J ������ 0 scriptdirecty scriptDirecty��   I  K L K p       M M ������ 0 
scriptname 
scriptName��   L  N O N p       P P ������ 0 icsdirectory icsDirectory��   O  Q R Q p       S S ������ 0 icsfilename icsFilename��   R  T U T p       V V ������ 0 xlsxdirectory xlsxDirectory��   U  W X W p       Y Y ������ 0 xlsxfilename xlsxFileName��   X  Z [ Z p       \ \ ������ 0 lookbacktime lookBackTime��   [  ] ^ ] p       _ _ ������ 0 	starttime 	startTime��   ^  ` a ` p       b b ������ 0 appname appName��   a  c d c l     ��������  ��  ��   d  e f e l     �� g h��   g   Set the global values    h � i i ,   S e t   t h e   g l o b a l   v a l u e s f  j k j l     �� l m��   l    You need to adjust these!    m � n n 4   Y o u   n e e d   t o   a d j u s t   t h e s e ! k  o p o l     �� q r��   q T N set scriptDirectory to "/Volumes/UDRIVE/Scripts/RemindersApp_Tasks_To_Excel/"    r � s s �   s e t   s c r i p t D i r e c t o r y   t o   " / V o l u m e s / U D R I V E / S c r i p t s / R e m i n d e r s A p p _ T a s k s _ T o _ E x c e l / " p  t u t l     v���� v r      w x w m      y y � z z � / V o l u m e s / I D R I V E / S i t e s / t e s t _ a n d _ s c r i p t s / R e m i n d e r s A p p _ T a s k s _ T o _ E x c e l / x o      ���� "0 scriptdirectory scriptDirectory��  ��   u  { | { l     �� } ~��   } - ' php script that processes the ICS file    ~ �   N   p h p   s c r i p t   t h a t   p r o c e s s e s   t h e   I C S   f i l e |  � � � l    ����� � r     � � � m     � � � � �  i c s . p h p � o      ���� 0 
scriptname 
scriptName��  ��   �  � � � l     �� � ���   � - ' set icsDirectory to "/Volumes/UDRIVE/"    � � � � N   s e t   i c s D i r e c t o r y   t o   " / V o l u m e s / U D R I V E / " �  � � � l    ����� � r     � � � m    	 � � � � �   / V o l u m e s / I D R I V E / � o      ���� 0 icsdirectory icsDirectory��  ��   �  � � � l    ����� � r     � � � m     � � � � �  T a s k s . i c s � o      ���� 0 icsfilename icsFilename��  ��   �  � � � l     �� � ���   � . ( set xlsxDirectory to "/Volumes/UDRIVE/"    � � � � P   s e t   x l s x D i r e c t o r y   t o   " / V o l u m e s / U D R I V E / " �  � � � l    ����� � r     � � � m     � � � � �   / V o l u m e s / I D R I V E / � o      ���� 0 xlsxdirectory xlsxDirectory��  ��   �  � � � l    ����� � r     � � � b     � � � l    ����� � I   �� ���
�� .sysoexecTEXT���     TEXT � m     � � � � �  d a t e   + % Y - % m - % d��  ��  ��   � m     � � � � � *   -   K F r a n k e   T a s k s . x l s x � o      ���� 0 xlsxfilename xlsxFileName��  ��   �  � � � l   ! � � � � r    ! � � � m     � � � � �  Y E A R L Y � o      ���� 0 lookbacktime lookBackTime � 0 *Options are DAILY, WEEKLY, MONTHLY, YEARLY    � � � � T O p t i o n s   a r e   D A I L Y ,   W E E K L Y ,   M O N T H L Y ,   Y E A R L Y �  � � � l  " 1 ����� � r   " 1 � � � c   " - � � � l  " ) ����� � I  " )�� ���
�� .sysoexecTEXT���     TEXT � m   " % � � � � �  d a t e   + % s��  ��  ��   � m   ) ,��
�� 
long � o      ���� 0 	starttime 	startTime��  ��   �  � � � l  2 9 ����� � r   2 9 � � � m   2 5 � � � � �  R e m i n d e r s � o      ���� 0 appname appName��  ��   �  � � � l     ��������  ��  ��   �  � � � l     �� � ���   � + % Check to see if Reminders is running    � � � � J   C h e c k   t o   s e e   i f   R e m i n d e r s   i s   r u n n i n g �  � � � l  : [ ����� � Z   : [ � ����� � >  : H � � � n   : F � � � 1   B F��
�� 
prun � 4   : B�� �
�� 
capp � o   > A���� 0 appname appName � m   F G��
�� boovtrue � I  K W�� ���
�� .miscactvnull��� ��� null � 4   K S�� �
�� 
capp � o   O R���� 0 appname appName��  ��  ��  ��  ��   �  � � � l  \ c � � � � I  \ c�� ���
�� .sysodelanull��� ��� nmbr � m   \ _ � � ?�      ��   � * $Give the application time to open up    � � � � H G i v e   t h e   a p p l i c a t i o n   t i m e   t o   o p e n   u p �  � � � l  d� ����� � Q   d� � � � � O   gu � � � k   mt � �  � � � I  m r������
�� .miscactvnull��� ��� null��  ��   �  � � � r   s z � � � l  s v ����� � b   s v � � � o   s t���� 0 icsdirectory icsDirectory � o   t u���� 0 icsfilename icsFilename��  ��   � o      ���� 0 savepath savePath �  � � � r   { � � � � 4   { ��� 
�� 
list  m    � � 
 T a s k s � o      ���� 0 mylist myList �  r   � � e   � � n   � �	 2  � ���
�� 
remi	 o   � ����� 0 mylist myList o      ���� 0 myreminders myReminders 

 r   � � e   � � n   � � m   � ���
�� 
nmbr o   � ����� 0 myreminders myReminders o      ���� $0 myreminderscount myRemindersCount  Q   �j k   �S  l  � �����   C = Must use keystroke because Reminders has poor script support    � z   M u s t   u s e   k e y s t r o k e   b e c a u s e   R e m i n d e r s   h a s   p o o r   s c r i p t   s u p p o r t �� O   �S k   �R  !  I  � ���"#
�� .prcskcodnull���     ****" m   � ����� x# ��$��
�� 
faal$ J   � �%% &��& m   � ���
�� eMdsKctl��  ��  ! '(' I  � ���)��
�� .sysodelanull��� ��� nmbr) m   � �** ?�      ��  ( +,+ l  � �-./- I  � ���0��
�� .prcskprsnull���     ctxt0 m   � �11 �22  f��  . 
 File   / �33  F i l e, 454 I  � ���6��
�� .sysodelanull��� ��� nmbr6 m   � �77 ?�      ��  5 898 I  � ��:�~
� .prcskprsnull���     ctxt: o   � ��}
�} 
ret �~  9 ;<; I  � ��|=�{
�| .sysodelanull��� ��� nmbr= m   � �>> ?�      �{  < ?@? l  � �ABCA I  � ��zD�y
�z .prcskprsnull���     ctxtD m   � �EE �FF  e�y  B  Export   C �GG  E x p o r t@ HIH I  � ��xJ�w
�x .sysodelanull��� ��� nmbrJ m   � �KK ?�      �w  I LML I  � ��vN�u
�v .prcskprsnull���     ctxtN o   � ��t
�t 
ret �u  M OPO I  � �sQ�r
�s .sysodelanull��� ��� nmbrQ m   � ��q�q �r  P RSR I �pT�o
�p .prcskprsnull���     ctxtT m  UU �VV 2 / V o l u m e s / I D R I V E / T a s k s . i c s�o  S WXW l 		�n�m�l�n  �m  �l  X YZY I 	�k[\
�k .prcskprsnull���     ctxt[ m  	]] �^^  a\ �j_�i
�j 
faal_ m  �h
�h eMdsKcmd�i  Z `a` I �gb�f
�g .sysodelanull��� ��� nmbrb m  cc ?�      �f  a ded I &�ef�d
�e .prcskprsnull���     ctxtf m  "gg �hh 2 / V o l u m e s / I D R I V E / T a s k s . i c s�d  e iji l ''�ckl�c  k   keystroke savePath   l �mm &   k e y s t r o k e   s a v e P a t hj non I ',�bp�a
�b .sysodelanull��� ��� nmbrp m  '(�`�` �a  o qrq I -4�_s�^
�_ .prcskprsnull���     ctxts o  -0�]
�] 
ret �^  r tut I 5:�\v�[
�\ .sysodelanull��� ��� nmbrv m  56�Z�Z �[  u wxw I ;B�Yy�X
�Y .prcskprsnull���     ctxty o  ;>�W
�W 
ret �X  x z{z I CJ�V|�U
�V .sysodelanull��� ��� nmbr| m  CF}} @      �U  { ~�T~ I KR�S�R
�S .prcskprsnull���     ctxt 1  KN�Q
�Q 
spac�R  �T   m   � ����                                                                                  sevs  alis    N  MACOS                          BD ����System Events.app                                              ����            ����  
 cu             CoreServices  0/:System:Library:CoreServices:System Events.app/  $  S y s t e m   E v e n t s . a p p    M A C O S  -System/Library/CoreServices/System Events.app   / ��  ��   R      �P��
�P .ascrerr ****      � ****� o      �O�O 0 errtxt errTxt� �N��M
�N 
errn� o      �L�L 0 errnum errNum�M   I [j�K��J
�K .ascrcmnt****      � ****� b  [f��� b  [d��� b  [`��� m  [^�� ���  E r r o r :  � o  ^_�I�I 0 errtxt errTxt� m  `c�� ���   � o  de�H�H 0 errnum errNum�J   ��� l kr���� I kr�G��F
�G .sysodelanull��� ��� nmbr� m  kn�E�E 
�F  � 7 1Wait because it takes a long time to export tasks   � ��� b W a i t   b e c a u s e   i t   t a k e s   a   l o n g   t i m e   t o   e x p o r t   t a s k s� ��� l ss�D���D  � Y S TODO: do important data extraction with AppleScript rather than dumping everything   � ��� �   T O D O :   d o   i m p o r t a n t   d a t a   e x t r a c t i o n   w i t h   A p p l e S c r i p t   r a t h e r   t h a n   d u m p i n g   e v e r y t h i n g� ��� l ss�C���C  � , & repeat with myReminder in myReminders   � ��� L   r e p e a t   w i t h   m y R e m i n d e r   i n   m y R e m i n d e r s� ��� l ss�B���B  � 2 , set theReminder to (the name of myReminder)   � ��� X   s e t   t h e R e m i n d e r   t o   ( t h e   n a m e   o f   m y R e m i n d e r )� ��� l ss�A���A  � B < set theReminderCreated to (the creation date of myReminder)   � ��� x   s e t   t h e R e m i n d e r C r e a t e d   t o   ( t h e   c r e a t i o n   d a t e   o f   m y R e m i n d e r )� ��@� l ss�?���?  �   end repeat	   � ���    e n d   r e p e a t 	�@   � m   g j��~                                                                                  rmnd  alis       MACOS                          BD ����Reminders.app                                                  ����            ����  
 cu             Applications  /:Applications:Reminders.app/     R e m i n d e r s . a p p    M A C O S  Applications/Reminders.app  / ��   � R      �>��
�> .ascrerr ****      � ****� o      �=�= 0 errtxt errTxt� �<��;
�< 
errn� o      �:�: 0 errnum errNum�;   � I }��9��8
�9 .ascrcmnt****      � ****� b  }���� b  }���� b  }���� m  }��� ���  E r r o r :  � o  ���7�7 0 errtxt errTxt� m  ���� ���   � o  ���6�6 0 errnum errNum�8  ��  ��   � ��� l     �5�4�3�5  �4  �3  � ��� l     �2�1�0�2  �1  �0  � ��� l �
��/�.� Q  �
���� k  ���� ��� l ���-���-  � &  tell application "System Events"   � ��� @ t e l l   a p p l i c a t i o n   " S y s t e m   E v e n t s "� ��� r  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� b  ����� m  ���� ���  p h p   - q� m  ���� ���   � 1  ���,
�, 
quot� o  ���+�+ "0 scriptdirectory scriptDirectory� o  ���*�* 0 
scriptname 
scriptName� 1  ���)
�) 
quot� m  ���� ���   � 1  ���(
�( 
quot� o  ���'�' 0 icsdirectory icsDirectory� o  ���&�& 0 icsfilename icsFilename� 1  ���%
�% 
quot� m  ���� ���   � 1  ���$
�$ 
quot� o  ���#�# 0 xlsxdirectory xlsxDirectory� o  ���"�" 0 xlsxfilename xlsxFileName� 1  ���!
�! 
quot� m  ���� ���   � o  ��� �  0 lookbacktime lookBackTime� o      �� 0 scriptcommand scriptCommand� ��� I �����
� .ascrcmnt****      � ****� b  ��   m  �� �  D o i n g :   o  ���� 0 scriptcommand scriptCommand�  �  r  �� I ����
� .sysoexecTEXT���     TEXT o  ���� 0 scriptcommand scriptCommand�   o      �� 00 resultsofscriptcommand resultsOfScriptCommand 	
	 r  �� n  �� 2 ���
� 
cpar o  ���� 00 resultsofscriptcommand resultsOfScriptCommand o      �� 0 scriptresult scriptResult
 � l ����    end tell    �  e n d   t e l l�  � R      �
� .ascrerr ****      � **** o      �� 0 errtxt errTxt ��
� 
errn o      �� 0 errnum errNum�  � I �
��
� .ascrcmnt****      � **** b  � b  � b  �  m  �� �  E r r o r :   o  ���� 0 errtxt errTxt m    �      o  �
�
 0 errnum errNum�  �/  �.  � !"! l     �	���	  �  �  " #$# l     �%&�  %   How long did it take   & �'' *   H o w   l o n g   d i d   i t   t a k e$ ()( l *��* r  +,+ c  -.- l /��/ I �0� 
� .sysoexecTEXT���     TEXT0 m  11 �22  d a t e   + % s�   �  �  . m  ��
�� 
long, o      ���� 0 endtime endTime�  �  ) 343 l *5����5 r  *676 c  &898 \  ":;: o  ���� 0 endtime endTime; o  !���� 0 	starttime 	startTime9 m  "%��
�� 
long7 o      ����  0 processingtime processingTime��  ��  4 <=< l +:>����> I +:��?��
�� .ascrcmnt****      � ****? b  +6@A@ b  +2BCB m  +.DD �EE " P r o c e s s i n g   t i m e :  C o  .1����  0 processingtime processingTimeA m  25FF �GG    s e c o n d s��  ��  ��  = H��H l     ��������  ��  ��  ��       ��IJ��  I ��
�� .aevtoappnull  �   � ****J ��K����LM��
�� .aevtoappnull  �   � ****K k    :NN  tOO  �PP  �QQ  �RR  �SS  �TT  �UU  �VV  �WW  �XX  �YY  �ZZ �[[ (\\ 3]] <����  ��  ��  L ������ 0 errtxt errTxt�� 0 errnum errNumM M y�� ��� ��� ��� ��� ��� ��� ��� ����� ��������� ���������������������������*1����EU]��g}����^�����������������������1����DF�� "0 scriptdirectory scriptDirectory�� 0 
scriptname 
scriptName�� 0 icsdirectory icsDirectory�� 0 icsfilename icsFilename�� 0 xlsxdirectory xlsxDirectory
�� .sysoexecTEXT���     TEXT�� 0 xlsxfilename xlsxFileName�� 0 lookbacktime lookBackTime
�� 
long�� 0 	starttime 	startTime�� 0 appname appName
�� 
capp
�� 
prun
�� .miscactvnull��� ��� null
�� .sysodelanull��� ��� nmbr�� 0 savepath savePath
�� 
list�� 0 mylist myList
�� 
remi�� 0 myreminders myReminders
�� 
nmbr�� $0 myreminderscount myRemindersCount�� x
�� 
faal
�� eMdsKctl
�� .prcskcodnull���     ****
�� .prcskprsnull���     ctxt
�� 
ret 
�� eMdsKcmd
�� 
spac�� 0 errtxt errTxt^ ������
�� 
errn�� 0 errnum errNum��  
�� .ascrcmnt****      � ****�� 

�� 
quot�� 0 scriptcommand scriptCommand�� 00 resultsofscriptcommand resultsOfScriptCommand
�� 
cpar�� 0 scriptresult scriptResult�� 0 endtime endTime��  0 processingtime processingTime��;�E�O�E�O�E�O�E�O�E�O�j �%E�O�E�Oa j a &E` Oa E` O*a _ /a ,e *a _ /j Y hOa j Oa 	*j O��%E` O*a a /E` O_ a -EE`  O_  a !,EE` "O �a # �a $a %a &kvl 'Oa (j Oa )j *Oa (j O_ +j *Oa (j Oa ,j *Oa (j O_ +j *Olj Oa -j *Oa .a %a /l *Oa j Oa 0j *Olj O_ +j *Olj O_ +j *Oa 1j O_ 2j *UW X 3 4a 5�%a 6%�%j 7Oa 8j OPUW X 3 4a 9�%a :%�%j 7O ha ;a <%_ =%�%�%_ =%a >%_ =%�%�%_ =%a ?%_ =%�%�%_ =%a @%�%E` AOa B_ A%j 7O_ Aj E` CO_ Ca D-E` EOPW X 3 4a F�%a G%�%j 7Oa Hj a &E` IO_ I_ a &E` JOa K_ J%a L%j 7ascr  ��ޭ