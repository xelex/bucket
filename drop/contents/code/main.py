# -*- coding: utf-8 -*-

#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.

# Written by Balázs Nagy <nxbalazs@gmail.com>
# Idea: nferencfx
# altered by Grundik

# Drop 2 host
# Based on Drop 2 Imageshack

##################################################
# Require: scrot - screen capture util ###########
################## http://linuxbrit.co.uk/scrot/ #
##################################################

import os
import pycurl
import StringIO
from pycurl import FORM_FILE
from re import search
from PyQt4.QtCore import *
from PyQt4.QtGui import *
from PyKDE4.kdecore import *
from PyKDE4.kdeui import *
from PyKDE4.kio import *
from PyKDE4.plasma import Plasma
from PyKDE4 import plasmascript

class DropToImgshack(plasmascript.Applet):
    def __init__(self, parent, args=None):
        plasmascript.Applet.__init__(self, parent)
 
    def init(self):
        self.setHasConfigurationInterface(False)
        self.setAspectRatioMode(Plasma.ConstrainedSquare)
        self.theme = Plasma.Svg(self)
        self.theme.setImagePath("widgets/background")
        self.setBackgroundHints(Plasma.Applet.DefaultBackground)
        self.layout = QGraphicsLinearLayout(self.applet)
        self.layout.setContentsMargins(0, 0, 0, 0)
        self.layout.setSpacing(0)
        self.setAcceptDrops(True)
        self.layout.setOrientation(Qt.Horizontal)
        self.icon = Plasma.IconWidget(KIcon("image-loading"), "", self.applet)
        self.layout.addItem(self.icon)
        self.resize(self.icon.iconSize())
        self.connect(self.icon, SIGNAL("clicked()"), self.takeScreen)

    #def showConfigurationInterface(self):
        #dialog = KPageDialog()
        #dialog.setFaceType(KPageDialog.List)
        #dialog.setButtons( KDialog.ButtonCode(KDialog.Ok | KDialog.Cancel) )
        #self.createConfigurationInterface(dialog)
        #dialog.exec_()

    def takeScreen(self):
        os.system("scrot /tmp/myscreen2upload.png")
        self.addr = "/tmp/myscreen2upload.png"
        self.check()
        os.remove("/tmp/myscreen2upload.png")

    def checknotif(self):
        homedir = os.path.expanduser("~")
        mydir = homedir+"/.kde/share/apps/drop2host-plasmoid/"
        myfile = mydir+"drop2host-plasmoid.notifyrc"
        if os.path.exists(mydir) == False:
            mystr = "[Global]\nIconName=image-loading\nComment=drop2host plasmoid\nName=drop2host\n\n[Event/image-link]\nName=Image link\nAction=Popup\n"
            os.mkdir(mydir)
            writef = open(myfile, "w")
            writef.write(str(mystr))
            writef.close()

    def dragEnterEvent(self, e):
        if e.mimeData().hasFormat('text/plain'):
            e.accept()
        else:
            e.ignore()

    def dropEvent(self, e):
        self.addr = e.mimeData().text()
        self.addr = str(self.addr)
        self.addr = self.addr.replace("file://", "")
        self.check()

    def notif(self, msg):
        self.checknotif()
        KNotification.event("image-link", msg, QPixmap(), None, KNotification.CloseOnTimeout, KComponentData("drop2host-plasmoid", "drop2host-plasmoid", KComponentData.SkipMainComponentRegistration))

    def upload(self, img):
        c = pycurl.Curl()
        params = [('userfile', (FORM_FILE, img)), ('action', 'post')]

        c.setopt(pycurl.URL, "http://ololo.cc/publish/publish.php")
        c.setopt(pycurl.HTTPHEADER, ["Except:"])
        c.setopt(pycurl.POST, 1)
        c.setopt(pycurl.HTTPPOST, params)

        b = StringIO.StringIO()
        c.setopt(pycurl.WRITEFUNCTION, b.write)
        c.perform()
        html = b.getvalue()

        image_link = search(r'<file_link>(.*)</file_link>', html).group(1)
        return image_link

    def check(self):
#        accept_imgs = ["jpg", "jpeg", "gif", "png", "bmp", "tif", "tiff", "JPG", "JPEG", "GIF", "PNG", "BMP", "TIF", "TIFF"]
#        if not self.addr.split('.')[-1] in accept_imgs:
#            self.notif("Invalid file")
#        else:
            link = self.upload(self.addr)
            clipboard = QApplication.clipboard()
            clipboard.setText(link)
            msg = "Succesfully uploaded: "+link+"<br> Link has been copied to your clipboard"
            self.notif(msg)


def CreateApplet(parent):
    return DropToImgshack(parent) 
