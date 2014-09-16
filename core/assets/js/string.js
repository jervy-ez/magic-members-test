// JavaScript Document
// email format
String.prototype.is_email=function(){
   return (/^[\w-\.]+\@[\w\.-]+\.[a-z]{2,4}$/.test(this));	
}
// empty test
String.prototype.is_empty=function(){
   return (this.search(/\S/)==-1)?true:false;	
}
// url slug
String.prototype.slug=function(){
   return this.toLowerCase().replace(/\s/g,'-').replace(/[^a-zA-Z0-9]/g,'-').replace(/-{2,}/g,'-').replace(/^-/,'').replace(/-$/,'');
}
// array keyslug
String.prototype.keyslug=function(){
   return this.toLowerCase().replace(/\s/g,'_').replace(/[^a-zA-Z0-9]/g,'_').replace(/_{2,}/g,'_').replace(/^_/,'').replace(/_$/,'');
}