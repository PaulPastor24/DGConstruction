'use client'

import { useState } from 'react'
import { makeStyles, mergeClasses, tokens } from '@fluentui/react-components'
import { Sidebar } from './sidebar'
import { TopNavigation } from './top-navigation'
import { SiteCommandCenter } from './site-command-center'
import { ProjectSnapshot } from './project-snapshot'
import { OperationalWorkspace } from './operational-workspace'
import { BottomWorkspace } from './bottom-workspace'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    height: '100vh',
    backgroundColor: tokens.colorNeutralBackground1,
  },
  main: {
    display: 'flex',
    flexDirection: 'column',
    flex: 1,
    overflow: 'auto',
  },
  content: {
    flex: 1,
    paddingTop: tokens.spacingVerticalL,
    paddingBottom: tokens.spacingVerticalXXL,
    paddingLeft: tokens.spacingHorizontalL,
    paddingRight: tokens.spacingHorizontalL,
    maxWidth: '1600px',
    marginLeft: 'auto',
    marginRight: 'auto',
    width: '100%',
  },
  section: {
    marginBottom: tokens.spacingVerticalXXL,
  },
})

export function SupervisorDashboard() {
  const styles = useStyles()
  const [sidebarOpen, setSidebarOpen] = useState(true)

  return (
    <div className={styles.root}>
      <Sidebar open={sidebarOpen} onToggle={() => setSidebarOpen(!sidebarOpen)} />
      <div className={styles.main}>
        <TopNavigation onMenuClick={() => setSidebarOpen(!sidebarOpen)} />
        <div className={styles.content}>
          <div className={styles.section}>
            <SiteCommandCenter />
          </div>
          <div className={styles.section}>
            <ProjectSnapshot />
          </div>
          <div className={styles.section}>
            <OperationalWorkspace />
          </div>
          <div className={styles.section}>
            <BottomWorkspace />
          </div>
        </div>
      </div>
    </div>
  )
}
